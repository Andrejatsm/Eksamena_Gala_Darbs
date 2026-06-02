<?php
session_start();
require '../includes/db.php';
require_once __DIR__ . '/../includes/lang.php';

header('Content-Type: application/json');

if (!isset($_SESSION['account_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Piekļuve liegta.']);
    exit();
}

$countQuery = static function (mysqli $conn, string $query): int {
    $result = $conn->query($query);
    if (!$result) {
        return 0;
    }
    $row = $result->fetch_assoc();
    return (int)($row['count'] ?? 0);
};

$buildStats = static function (mysqli $conn) use ($countQuery): array {
    return [
        'users' => $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'user' AND status = 'active'"),
        'psychologists' => $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'psychologist' AND status = 'active'"),
        'pendingPsychologists' => $countQuery($conn, "SELECT COUNT(*) AS count FROM accounts WHERE role = 'psychologist' AND status = 'pending'"),
        'appointments' => $countQuery($conn, "SELECT COUNT(*) AS count FROM appointments WHERE status IN ('pending', 'approved')"),
        'articles' => $countQuery($conn, "SELECT COUNT(*) AS count FROM articles WHERE is_published = 0"),
        'tests' => $countQuery($conn, "SELECT COUNT(*) AS count FROM tests WHERE status = 'published'"),
    ];
};

$hasLinkedAppointments = static function (mysqli $conn, int $accountId, string $role): bool {
    if ($role === 'psychologist') {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE psychologist_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, pārbaudot saistītos pierakstus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return ((int)($row['count'] ?? 0)) > 0;
    }

    if ($role === 'user') {
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE user_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, pārbaudot saistītos pierakstus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;
        return ((int)($row['count'] ?? 0)) > 0;
    }

    return false;
};

$cleanupLinkedData = static function (mysqli $conn, int $accountId, string $role): void {
    if ($role === 'psychologist') {
        $stmt = $conn->prepare("DELETE cm FROM chat_messages cm INNER JOIN appointments a ON cm.appointment_id = a.id WHERE a.psychologist_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, tīrot psihologa čata datus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE vr FROM video_rooms vr INNER JOIN appointments a ON vr.appointment_id = a.id WHERE a.psychologist_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, tīrot psihologa video datus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM appointments WHERE psychologist_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, dzēšot psihologa pierakstus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();
    }

    if ($role === 'user') {
        $stmt = $conn->prepare("DELETE cm FROM chat_messages cm INNER JOIN appointments a ON cm.appointment_id = a.id WHERE a.user_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, tīrot lietotāja čata datus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE vr FROM video_rooms vr INNER JOIN appointments a ON vr.appointment_id = a.id WHERE a.user_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, tīrot lietotāja video datus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM appointments WHERE user_account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, dzēšot lietotāja pierakstus.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();
    }
};

$action = (string)($_POST['action'] ?? '');
$accountId = (int)($_POST['account_id'] ?? 0);

if ($accountId <= 0 || $action === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Trūkst darbības datu.']);
    exit();
}

try {
    if ($action === 'approve_psych') {
        $conn->begin_transaction();

        $stmt = $conn->prepare("UPDATE psychologist_profiles SET approved_at = NOW() WHERE account_id = ?");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("UPDATE accounts SET status = 'active' WHERE id = ? AND role = 'psychologist'");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $updated = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$updated) {
            throw new RuntimeException(t('psych_approve_error'));
        }

        $conn->commit();

        $stmt = $conn->prepare("SELECT a.email, COALESCE(NULLIF(p.full_name, ''), a.username) AS full_name FROM accounts a JOIN psychologist_profiles p ON p.account_id = a.id WHERE a.id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $accountId);
            $stmt->execute();
            $psychRow = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($psychRow && filter_var($psychRow['email'], FILTER_VALIDATE_EMAIL)) {
                require_once __DIR__ . '/../includes/mail.php';

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $loginUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/login.php';
                $name = htmlspecialchars(trim($psychRow['full_name'] ?: $psychRow['email']), ENT_QUOTES, 'UTF-8');
                $lang = 'lv';
                $subject = t('psych_approval_email_subject');
                $htmlBody = build_approval_email_html($name, $loginUrl, $lang);
                send_html_email($psychRow['email'], $subject, $htmlBody);
            }
        }

        $message = t('psych_approved');
    } elseif ($action === 'reject_psych') {
        $stmt = $conn->prepare("UPDATE accounts SET status = 'rejected' WHERE id = ? AND role = 'psychologist'");
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $updated = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$updated) {
            throw new RuntimeException('Neizdevās noraidīt psihologa profilu.');
        }

        $message = 'Psihologa profils noraidīts.';
    } elseif ($action === 'delete_psych') {
        $conn->begin_transaction();

        $cleanupLinkedData($conn, $accountId, 'psychologist');

        $stmt = $conn->prepare("DELETE FROM psychologist_profiles WHERE account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, dzēšot psihologa profilu.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ? AND role = 'psychologist'");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, dzēšot psihologa kontu.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new RuntimeException('Neizdevās izdzēst psihologa kontu.');
        }

        $conn->commit();
        $message = 'Psihologa konts un saistītie dati izdzēsti.';
    } elseif ($action === 'save_account_profile') {
        $email = trim((string)($_POST['email'] ?? ''));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $status = trim((string)($_POST['status'] ?? 'active'));
        $password = trim((string)($_POST['password'] ?? ''));
        $specialization = trim((string)($_POST['specialization'] ?? ''));
        $experienceYears = min(50, max(0, (int)($_POST['experience_years'] ?? 0)));
        $description = trim((string)($_POST['description'] ?? ''));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Lūdzu ievadiet derīgu e-pasta adresi.');
        }

        $allowedStatuses = ['active', 'pending', 'rejected', 'disabled'];
        if (!in_array($status, $allowedStatuses, true)) {
            $status = 'active';
        }

        $stmt = $conn->prepare("SELECT role FROM accounts WHERE id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $result = $stmt->get_result();
        $accountRow = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if (!$accountRow) {
            throw new RuntimeException('Konts netika atrasts.');
        }

        $role = (string)($accountRow['role'] ?? 'user');
        $conn->begin_transaction();

        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE accounts SET email = ?, phone = ?, status = ?, password_hash = ? WHERE id = ?");
            if (!$stmt) {
                throw new RuntimeException('Datubāzes kļūda, atjauninot kontu.');
            }
            $stmt->bind_param('ssssi', $email, $phone, $status, $passwordHash, $accountId);
        } else {
            $stmt = $conn->prepare("UPDATE accounts SET email = ?, phone = ?, status = ? WHERE id = ?");
            if (!$stmt) {
                throw new RuntimeException('Datubāzes kļūda, atjauninot kontu.');
            }
            $stmt->bind_param('sssi', $email, $phone, $status, $accountId);
        }
        $stmt->execute();
        if ($stmt->errno) {
            $stmt->close();
            throw new RuntimeException('Neizdevās atjaunināt konta informāciju.');
        }
        $stmt->close();

        if ($role === 'psychologist') {
            if ($specialization === '') {
                throw new RuntimeException('Lūdzu norādiet psihologa specializāciju.');
            }

            $stmt = $conn->prepare("UPDATE psychologist_profiles SET specialization = ?, experience_years = ?, description = ? WHERE account_id = ?");
            if (!$stmt) {
                throw new RuntimeException('Datubāzes kļūda, atjauninot psihologa profilu.');
            }
            $stmt->bind_param('sisi', $specialization, $experienceYears, $description, $accountId);
            $stmt->execute();
            if ($stmt->errno) {
                $stmt->close();
                throw new RuntimeException('Neizdevās atjaunināt psihologa profilu.');
            }
            $stmt->close();

            if ($status === 'active') {
                $stmt = $conn->prepare("UPDATE psychologist_profiles SET approved_at = COALESCE(approved_at, NOW()) WHERE account_id = ?");
                if (!$stmt) {
                    throw new RuntimeException('Datubāzes kļūda, atjauninot apstiprinājuma datumu.');
                }
                $stmt->bind_param('i', $accountId);
                $stmt->execute();
                $stmt->close();
                
                // Send approval email
                $stmtEmail = $conn->prepare("SELECT email, COALESCE(NULLIF(full_name, ''), (SELECT username FROM accounts WHERE id = ?)) AS full_name FROM psychologist_profiles p JOIN accounts a ON p.account_id = a.id WHERE a.id = ?");
                if ($stmtEmail) {
                    $stmtEmail->bind_param('ii', $accountId, $accountId);
                    $stmtEmail->execute();
                    $emailRow = $stmtEmail->get_result()->fetch_assoc();
                    $stmtEmail->close();
                    
                    if ($emailRow && filter_var($emailRow['email'], FILTER_VALIDATE_EMAIL)) {
                        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                        $loginUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/auth/login.php';
                        $name = htmlspecialchars(trim($emailRow['full_name'] ?: $emailRow['email']), ENT_QUOTES, 'UTF-8');
                        $lang = 'lv';
                        $subject = t('psych_approval_email_subject');
                        $htmlBody = build_approval_email_html($name, $loginUrl, $lang);
                        send_html_email($emailRow['email'], $subject, $htmlBody);
                    }
                }
            }
        }
        $conn->commit();
        $message = 'Profils ir veiksmīgi atjaunināts.';
    } elseif ($action === 'delete_user') {
        $conn->begin_transaction();

        $cleanupLinkedData($conn, $accountId, 'user');

        $stmt = $conn->prepare("DELETE FROM user_profiles WHERE account_id = ?");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, dzēšot lietotāja profilu.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM accounts WHERE id = ? AND role = 'user'");
        if (!$stmt) {
            throw new RuntimeException('Datubāzes kļūda, dzēšot lietotāja kontu.');
        }
        $stmt->bind_param('i', $accountId);
        $stmt->execute();
        $deleted = $stmt->affected_rows > 0;
        $stmt->close();

        if (!$deleted) {
            throw new RuntimeException('Neizdevās izdzēst lietotāja kontu.');
        }

        $conn->commit();
        $message = 'Lietotāja konts un saistītie dati izdzēsti.';
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'stats' => $buildStats($conn),
    ]);
} catch (RuntimeException $e) {
    if ($conn->errno) {
        $conn->rollback();
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage() ?: 'Notika kļūda. Lūdzu, pārbaudiet datus un mēģiniet vēlreiz.',
    ]);
} catch (Throwable $e) {
    if ($conn->errno) {
        $conn->rollback();
    }

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Notika neparedzēta servera kļūda.',
    ]);
}
?>
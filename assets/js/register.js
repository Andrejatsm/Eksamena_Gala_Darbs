document.addEventListener('DOMContentLoaded', () => {
    const roleRadios = document.querySelectorAll('input[name="role"]');
    const psychologistFields = document.getElementById('psychologist-fields');

    if (!roleRadios.length || !psychologistFields) {
        return;
    }

    const toggleFields = () => {
        const selectedRole = document.querySelector('input[name="role"]:checked');
        if (!selectedRole) {
            psychologistFields.classList.add('hidden');
            return;
        }

        if (selectedRole.value === 'psychologist') {
            psychologistFields.classList.remove('hidden');
        } else {
            psychologistFields.classList.add('hidden');
        }
    };

    roleRadios.forEach((radio) => radio.addEventListener('change', toggleFields));
    toggleFields();
});

/**
 * JavaScript du BackOffice
 * Guerre Iran - Administration
 */

document.addEventListener('DOMContentLoaded', function () {

    // Toggle Sidebar sur mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const adminSidebar = document.getElementById('adminSidebar');

    if (sidebarToggle && adminSidebar) {
        sidebarToggle.addEventListener('click', function () {
            adminSidebar.classList.toggle('open');
        });

        // Fermer la sidebar en cliquant en dehors
        document.addEventListener('click', function (e) {
            if (!adminSidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                adminSidebar.classList.remove('open');
            }
        });
    }

    // Auto-hide des alertes après 5 secondes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.3s ease';
            setTimeout(function () {
                alert.remove();
            }, 300);
        }, 5000);
    });

    // Confirmation de suppression
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(function (button) {
        button.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    // Génération automatique de slug
    const titleInput = document.getElementById('titre');
    const slugInput = document.getElementById('slug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.dataset.modified) {
                slugInput.value = slugify(this.value);
            }
        });

        slugInput.addEventListener('input', function () {
            this.dataset.modified = 'true';
        });
    }

    // Upload d'image avec prévisualisation
    const uploadZones = document.querySelectorAll('.upload-zone');
    uploadZones.forEach(function (zone) {
        const input = zone.querySelector('input[type="file"]');
        const preview = zone.querySelector('.upload-preview');

        zone.addEventListener('click', function () {
            input.click();
        });

        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('dragover');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('dragover');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                handleFileSelect(input, preview);
            }
        });

        input.addEventListener('change', function () {
            handleFileSelect(this, preview);
        });
    });

    // Compteur de caractères pour les champs SEO
    const charCountInputs = document.querySelectorAll('[data-maxlength]');
    charCountInputs.forEach(function (input) {
        const maxLength = parseInt(input.getAttribute('data-maxlength'));
        const counter = document.createElement('span');
        counter.className = 'char-counter text-muted';
        counter.style.fontSize = '0.875rem';
        counter.style.marginTop = '4px';
        counter.style.display = 'block';

        input.parentNode.appendChild(counter);

        function updateCounter() {
            const remaining = maxLength - input.value.length;
            counter.textContent = `${input.value.length}/${maxLength} caractères`;
            counter.style.color = remaining < 0 ? 'var(--error-color)' : 'var(--text-light)';
        }

        input.addEventListener('input', updateCounter);
        updateCounter();
    });

});

/**
 * Convertir une chaîne en slug
 */
function slugify(text) {
    return text
        .toString()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-+/, '')
        .replace(/-+$/, '');
}

/**
 * Gérer la sélection d'un fichier
 */
function handleFileSelect(input, preview) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            if (preview) {
                preview.innerHTML = '<img src="' + e.target.result + '" alt="Aperçu">';
            }
        };

        reader.readAsDataURL(file);
    }
}

/**
 * Afficher une notification
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.maxWidth = '400px';

    document.body.appendChild(notification);

    setTimeout(function () {
        notification.style.opacity = '0';
        notification.style.transition = 'opacity 0.3s ease';
        setTimeout(function () {
            notification.remove();
        }, 300);
    }, 3000);
}

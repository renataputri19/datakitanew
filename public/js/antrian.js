/**
 * Antrian System JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const modal = document.getElementById('modalPilihLoket');
    const openModalBtn = document.getElementById('panggilAntrianBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const cancelModalBtn = document.getElementById('cancelModal');
    const tampilkanBtn = document.getElementById('tampilkanAntrian');
    const loketSelect = document.getElementById('loketAntrian');

    // Flow steps
    const flowSteps = document.querySelectorAll('.antrian-step');

    // Open modal
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function() {
            // Load loket options
            loadLoketOptions();
            modal.classList.remove('hidden');
        });
    }

    // Close modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    }

    if (cancelModalBtn) {
        cancelModalBtn.addEventListener('click', function() {
            modal.classList.add('hidden');
        });
    }

    // Handle outside click
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }

    // Handle tampilkan button
    if (tampilkanBtn) {
        tampilkanBtn.addEventListener('click', function() {
            const loket = loketSelect.value;
            if (loket !== '') {
                // Store selected loket in localStorage
                localStorage.setItem('_loket', loket);
                // Redirect to panggilan page
                window.location.href = tampilkanBtn.getAttribute('data-url');
            } else {
                showNotification('Silahkan pilih loket terlebih dahulu', 'error');
            }
        });
    }

    // Function to load loket options
    function loadLoketOptions() {
        if (!loketSelect) return;
        
        // Clear existing options except the first one
        while (loketSelect.options.length > 1) {
            loketSelect.remove(1);
        }

        // Fetch loket data from API
        const apiUrl = loketSelect.getAttribute('data-api-url');
        if (!apiUrl) return;
        
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    data.forEach(loket => {
                        const option = document.createElement('option');
                        option.value = loket.no_loket;
                        option.textContent = loket.nama_loket;
                        loketSelect.appendChild(option);
                    });
                } else {
                    // Add default options if no data is returned
                    const defaultLokets = [
                        { no_loket: "1", nama_loket: "Loket 1" },
                        { no_loket: "2", nama_loket: "Loket 2" },
                        { no_loket: "3", nama_loket: "Loket 3" }
                    ];

                    defaultLokets.forEach(loket => {
                        const option = document.createElement('option');
                        option.value = loket.no_loket;
                        option.textContent = loket.nama_loket;
                        loketSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading loket options:', error);
                // Add default options if there's an error
                const defaultLokets = [
                    { no_loket: "1", nama_loket: "Loket 1" },
                    { no_loket: "2", nama_loket: "Loket 2" },
                    { no_loket: "3", nama_loket: "Loket 3" }
                ];

                defaultLokets.forEach(loket => {
                    const option = document.createElement('option');
                    option.value = loket.no_loket;
                    option.textContent = loket.nama_loket;
                    loketSelect.appendChild(option);
                });
            });
    }

    // Function to show notifications
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `antrian-notification antrian-notification-${type}`;
        notification.innerHTML = `
            <div class="antrian-notification-content">
                <span>${message}</span>
            </div>
        `;

        // Add to document
        document.body.appendChild(notification);

        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Remove after delay
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
});

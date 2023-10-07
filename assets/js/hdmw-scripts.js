document.addEventListener("DOMContentLoaded", function() {

    function updateMenuVisibility(hiddenItems) {
        const adminMenu = document.querySelectorAll("ul#adminmenu li");
        adminMenu.forEach(function(el) {
            el.classList.remove('initially-hidden'); // remove the initially-hidden class
        });
        hiddenItems.forEach(function(item) {
            const element = document.querySelector(`ul#adminmenu li a[href$="${item}"]`);
            if (element) {
                element.closest("li").classList.add('initially-hidden'); // add the initially-hidden class
            }
        });
    }

    // Initial state update
    updateMenuVisibility(hdmwVars.hiddenItems);

    // Real-time UI updates
    const checkboxes = document.querySelectorAll('input[name="hidden_admin_menu_items[]"]');

    checkboxes.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const currentHiddenItems = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.value);

            updateMenuVisibility(currentHiddenItems);

            // AJAX call to update the backend
            const ajaxUrl = `${ajaxurl}?action=update_hidden_items`;
            const formData = new FormData();
            formData.append('hiddenItems', JSON.stringify(currentHiddenItems));
            formData.append('nonce', hdmwVars.nonce);

            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    // Handle the failure case appropriately for your application
                }
            })
            .catch(error => {
                // Handle the error appropriately for your application
            });
        });
    });
});

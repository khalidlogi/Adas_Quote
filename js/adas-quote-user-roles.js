jQuery(document).ready(function($) {
    var roles = adasQuoteUserRoles.roles;
    var selectedRoles = adasQuoteUserRoles.savedRoles.split(',').filter(Boolean);

    // Populate role dropdown
    var $roleDropdown = $('#role-dropdown');
    $.each(roles, function(role, name) {
        $roleDropdown.append('<div class="role-option" data-role="' + role + '">' + name + '</div>');
    });

    // Toggle dropdown
    $('#quote-user-roles').on('click', function() {
        $roleDropdown.toggle();
    });

    // Select role
    $(document).on('click', '.role-option', function() {
        var role = $(this).data('role');
        var name = $(this).text();
        if (selectedRoles.indexOf(role) === -1) {
            selectedRoles.push(role);
            updateSelectedRoles();
        }
        $roleDropdown.hide();
    });

    // Remove role
    $(document).on('click', '.adas-role-tag .remove', function() {
        var role = $(this).parent().data('role');
        selectedRoles = selectedRoles.filter(function(r) { return r !== role; });
        updateSelectedRoles();
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.role-select-container').length) {
            $roleDropdown.hide();
        }
    });

    function updateSelectedRoles() {
        var $selectedRoles = $('#selected-roles');
        $selectedRoles.empty();
        selectedRoles.forEach(function(role) {
            $selectedRoles.append('<span class="adas-role-tag" data-role="' + role + '">' + roles[role] + '<span class="remove">×</span></span>');
        });
        $('#quote-user-roles-hidden').val(selectedRoles.join(','));
    }

    // Initialize selected roles
    updateSelectedRoles();
});
<?php
session_start();

/**
 * Check if the current user has permission for a given action.
 * 
 * @param string $action The action to check permission for. 
 * 
 * @param int|null $patient_id
 * @return bool 
 */
function check_permission($action, $patient_id = null) {
    if (!isset($_SESSION['role'])) {
        return false;
    }

    $role = $_SESSION['role'];
    $permissions = [
        'admin' => ['add', 'edit', 'delete', 'view', 'assign_insurance'],
        'nurse' => ['add', 'view', 'assign_insurance'],
        'doctor' => ['view'],
        'laboratory_staff' => ['view'],
        'patient' => ['view_own_insurance']
    ];

    // Normalize role to lowercase
    $role = strtolower($role);

    if (!array_key_exists($role, $permissions)) {
        return false;
    }

    // Special case for patient role viewing own insurance
    if ($role === 'patient') {
        if ($action === 'view' && isset($_SESSION['patient_id']) && $patient_id !== null) {
            return $_SESSION['patient_id'] == $patient_id;
        }
        return false;
    }

    // For other roles, check if action is allowed
    if ($action === 'assign_insurance') {
        // Only admin and nurse can assign insurance
        return in_array('assign_insurance', $permissions[$role]);
    }

    return in_array($action, $permissions[$role]);
}
?>

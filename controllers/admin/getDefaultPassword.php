<?php
function generateDefaultPassword() {
    return 'Default@123';
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}
?> 
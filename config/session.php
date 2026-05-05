<?php
if (session_status()===PHP_SESSION_NONE) session_start();

function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function isLoggedIn(): bool { return isset($_SESSION['user']); }

function hasRole(string ...$roles): bool {
    $u = currentUser();
    return $u && in_array($u['role'], $roles);
}
function isOwner(): bool { return hasRole('Owner'); }
function isSysAdmin(): bool { return hasRole('Owner','System Admin'); }
function isAdmin(): bool { return hasRole('Owner','System Admin','Administrator'); }
function isStaff(): bool { return hasRole('Owner','System Admin','Administrator','Staff'); }

function requireLogin(string ...$roles): array {
    if (!isLoggedIn()) { header('Location: /auth/login.php'); exit; }
    $u = currentUser();
    if ($roles && !in_array($u['role'], $roles)) {
        header('Location: /auth/login.php?error=access_denied'); exit;
    }
    return $u;
}
function requireOwner(): array    { return requireLogin('Owner'); }
function requireSysAdmin(): array { return requireLogin('Owner','System Admin'); }
function requireAdmin(): array    { return requireLogin('Owner','System Admin','Administrator'); }
function requireStaff(): array    { return requireLogin('Owner','System Admin','Administrator','Staff'); }

function getDashboardUrl(?array $user = null): string {
    $u = $user ?? currentUser();
    if (!$u) return '/auth/login.php';
    return match($u['role']) {
        'Owner'         => '../owner/index.php',
        'System Admin'  => '../sysadmin/index.php',
        'Administrator' => '../admin/index.php',
        'Staff'         => '../staff/index.php',
        default         => '../user/dashboard.php',
    };
}

function jsonResp(array $data, int $code=200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data); exit;
}

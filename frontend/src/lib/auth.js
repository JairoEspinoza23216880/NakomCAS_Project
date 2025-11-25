// Constantes de roles
export const ROLES = {
    ADMIN_VENDEDOR: 1,
    CLIENTE: 2
};

/**
 * Normaliza el rol del usuario para compatibilidad con diferentes formatos del backend
 * @param {Object} user - Objeto de usuario
 * @returns {number} ID del rol normalizado
 */
function normalizeUserRole(user) {
    if (!user) return null;

    // Si ya tiene user_role_id, usarlo
    if (user.user_role_id !== undefined) {
        return user.user_role_id;
    }

    // Si tiene 'role' como string, convertirlo
    if (user.role) {
        // Mapeo de strings a IDs
        const roleMap = {
            'admin': ROLES.ADMIN_VENDEDOR,
            'administrador': ROLES.ADMIN_VENDEDOR,
            'vendedor': ROLES.ADMIN_VENDEDOR,
            'seller': ROLES.ADMIN_VENDEDOR,
            'cliente': ROLES.CLIENTE,
            'customer': ROLES.CLIENTE,
            'user': ROLES.CLIENTE
        };

        return roleMap[user.role.toLowerCase()] || null;
    }

    return null;
}

/**
 * Obtiene el usuario desde localStorage (cliente)
 * @returns {Object|null} Usuario o null si no está autenticado
 */
export function getUserFromStorage() {
    if (typeof window === 'undefined') return null;

    const token = localStorage.getItem('authToken');
    const userJson = localStorage.getItem('user');

    if (!token || !userJson) {
        return null;
    }

    try {
        const user = JSON.parse(userJson);

        // Normalizar el rol si es necesario
        const roleId = normalizeUserRole(user);
        if (roleId !== null && user.user_role_id === undefined) {
            user.user_role_id = roleId;
        }

        return user;
    } catch (error) {
        console.error('Error parsing user from localStorage:', error);
        return null;
    }
}

/**
 * Verifica si el usuario es Admin/Vendedor
 * @param {Object} user - Objeto de usuario
 * @returns {boolean}
 */
export function isAdmin(user) {
    if (!user) return false;
    const roleId = normalizeUserRole(user);
    return roleId === ROLES.ADMIN_VENDEDOR;
}

/**
 * Verifica si el usuario es Cliente
 * @param {Object} user - Objeto de usuario
 * @returns {boolean}
 */
export function isCliente(user) {
    if (!user) return false;
    const roleId = normalizeUserRole(user);
    return roleId === ROLES.CLIENTE;
}

/**
 * Verifica si el usuario tiene alguno de los roles permitidos
 * @param {Object} user - Objeto de usuario
 * @param {number[]} allowedRoles - Array de roles permitidos
 * @returns {boolean}
 */
export function hasRole(user, allowedRoles) {
    if (!user || !allowedRoles || allowedRoles.length === 0) {
        return false;
    }
    const roleId = normalizeUserRole(user);
    return allowedRoles.includes(roleId);
}

/**
 * Verifica autenticación y redirige si es necesario (cliente)
 * @param {number[]} allowedRoles - Roles permitidos para acceder
 */
export function protectRoute(allowedRoles = [ROLES.ADMIN_VENDEDOR]) {
    if (typeof window === 'undefined') return;

    const user = getUserFromStorage();

    // Si no está autenticado, redirigir a login
    if (!user) {
        window.location.href = '/Login';
        return;
    }

    // Si no tiene el rol permitido, redirigir a unauthorized
    if (!hasRole(user, allowedRoles)) {
        window.location.href = '/unauthorized';
        return;
    }
}

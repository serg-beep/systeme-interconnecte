import { useAuthStore } from '../stores/auth.js'

export function usePermissions() {
    const auth = useAuthStore()

    function peutFaire(permission) {
        return auth.hasPermission(permission)
    }

    function estAdmin() {
        return auth.hasAnyRole('admin', 'super_admin')
    }

    function estGestionnaire() {
        return estAdmin() || auth.hasRole('gestionnaire')
    }

    function estOperateur() {
        return auth.hasRole('operateur')
    }

    return { peutFaire, estAdmin, estGestionnaire, estOperateur }
}

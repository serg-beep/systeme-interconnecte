import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth.js'

const pages = {
    login: () => import('../views/auth/LoginView.vue'),
    register: () => import('../views/auth/RegisterView.vue'),
    registerParticulier: () => import('../views/auth/RegisterParticulierView.vue'),
    layout: () => import('../components/layouts/AppLayout.vue'),
    siteLayout: () => import('../components/layouts/SiteLayout.vue'),
    home: () => import('../views/site/HomeView.vue'),
    annuairePublic: () => import('../views/site/AnnuairePublicView.vue'),
    actualites: () => import('../views/site/ActualitesView.vue'),
    contact:    () => import('../views/site/ContactView.vue'),
    apropos:    () => import('../views/site/AProposView.vue'),
    conditions:     () => import('../views/site/ConditionsView.vue'),
    confidentialite: () => import('../views/site/ConfidentialiteView.vue'),
    serviceDetail: () => import('../views/site/ServiceDetailView.vue'),
    mesAbonnements: () => import('../views/site/MesAbonnementsView.vue'),
    publication: () => import('../components/site/Publication.vue'),
    profilPublic: () => import('../components/site/profilpublic.vue'),
    dashboard: () => import('../views/dashboard/DashboardView.vue'),
    alertes: () => import('../views/alertes/AlertesView.vue'),
    requetes: () => import('../views/requetes/RequetesView.vue'),
    demandes: () => import('../views/demandes/DemandesView.vue'),
    conversations: () => import('../views/conversations/ConversationsView.vue'),
    partenaires: () => import('../views/partenaires/PartenairesView.vue'),
    documents: () => import('../views/documents/DocumentsView.vue'),
    annuaire:          () => import('../views/annuaire/AnnuaireView.vue'),
    actualitesAdmin:   () => import('../views/actualites/ActualitesAdminView.vue'),
    notifications: () => import('../views/notifications/NotificationsView.vue'),
    moderation: () => import('../views/moderation/ModerationView.vue'),
    profil: () => import('../views/profil/ProfilView.vue'),
    adminUsers: () => import('../views/admin/UsersView.vue'),
    adminAudit: () => import('../views/admin/AuditView.vue'),
    adminEntreprises: () => import('../views/admin/EntreprisesView.vue'),
}

const routes = [
    // ── Site public (avec navbar publique) ──────
    {
        path: '/',
        component: pages.siteLayout,
        children: [
            { path: '',                   name: 'home',            component: pages.home },
            { path: 'annuaire-public',    name: 'annuaire-public', component: pages.annuairePublic },
            { path: 'actualites',         name: 'actualites',      component: pages.actualites },
            { path: 'contact',            name: 'contact',         component: pages.contact },
            { path: 'a-propos',           name: 'a-propos',        component: pages.apropos },
            { path: 'conditions',          name: 'conditions',       component: pages.conditions },
            { path: 'confidentialite',    name: 'confidentialite',  component: pages.confidentialite },
            { path: 'service/:id',        name: 'service-detail',  component: pages.serviceDetail },
            { path: 'mes-abonnements',    name: 'mes-abonnements', component: pages.mesAbonnements, meta: { requiresAuth: true } },
            { path: 'publication/:id',    name: 'publication',     component: pages.publication },
            { path: 'profil-public/:id',  name: 'profil-public',   component: pages.profilPublic },
        ]
    },
    {
        path: '/login',
        name: 'login',
        component: pages.login,
        meta: { guest: true }
    },
    {
        path: '/register',
        name: 'register',
        component: pages.register,
        meta: { guest: true }
    },
    {
        path: '/register-particulier',
        name: 'register-particulier',
        component: pages.registerParticulier,
        meta: { guest: true }
    },
    // ── App protégée (avec sidebar) ─────────────
    {
        path: '/app',
        component: pages.layout,
        meta: { requiresAuth: true },
        children: [
            { path: '',              redirect: '/app/dashboard' },
            { path: 'dashboard',     name: 'dashboard',     component: pages.dashboard },
            { path: 'alertes',       name: 'alertes',       component: pages.alertes },
            { path: 'requetes',      name: 'requetes',      component: pages.requetes },
            { path: 'demandes',      name: 'demandes',      component: pages.demandes },
            { path: 'conversations', name: 'conversations', component: pages.conversations },
            { path: 'partenaires',   name: 'partenaires',   component: pages.partenaires },
            { path: 'documents',     name: 'documents',     component: pages.documents },
            { path: 'annuaire',      name: 'annuaire',      component: pages.annuaire },
            { path: 'actualites',    name: 'actualites-admin', component: pages.actualitesAdmin, meta: { adminOnly: true } },
            { path: 'notifications', name: 'notifications', component: pages.notifications },
            { path: 'moderation',    name: 'moderation',    component: pages.moderation },
            { path: 'profil',        name: 'profil',        component: pages.profil },
            { path: 'admin/users',       name: 'admin-users',       component: pages.adminUsers, meta: { adminOnly: true } },
            { path: 'admin/audit',       name: 'admin-audit',       component: pages.adminAudit, meta: { adminOnly: true } },
            { path: 'admin/entreprises', name: 'admin-entreprises', component: pages.adminEntreprises, meta: { adminOnly: true } },
        ]
    },
    { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0, behavior: 'instant' }
    }
})

router.beforeEach((to, from, next) => {
    const auth = useAuthStore()
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        next({ name: 'login' })
    } else if (to.meta.guest && auth.isAuthenticated) {
        next({ name: 'dashboard' })
    } else if (to.meta.adminOnly && !auth.hasAnyRole('admin', 'super_admin')) {
        next({ name: 'dashboard' })
    } else {
        next()
    }
})

export function prefetchAppRoutes() {
    Object.entries(pages).forEach(([name, loader]) => {
        if (!['login', 'register', 'layout', 'siteLayout'].includes(name)) {
            loader()
        }
    })
}

export default router

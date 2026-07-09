<script setup>
import FloatingConfigurator from '@/components/FloatingConfigurator.vue';
import { ref, onBeforeMount } from 'vue';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

const toast = useToast();
const email = ref('');
const password = ref('');
const checked = ref(false);
const submitted = ref(false);
const errorMessage = ref('');

const loginUser = () => {
    submitted.value = true;
    errorMessage.value = '';

    axios
        .post(`/api/login`, {
            email: email.value.toLowerCase(),
            password: password.value
        })
        .then((response) => {
            localStorage.setItem('token', response.data.access_token);
            localStorage.setItem('user', JSON.stringify(response.data.user));
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Sessão iniciada com sucesso.',
                life: 3000
            });

            if (response.data.user.role_id == 1 || response.data.user.role_id == 2) {
                window.location.href = '/admin/dashboard';
            } else if (response.data.user.role_id == 3) {
                window.location.href = '/waiter/pdv';
            } else if (response.data.user.role_id == 4) {
                window.location.href = '/kitchen/pdv';
            } else if (response.data.user.role_id == 5) {
                window.location.href = '/bar/pdv';
            } else if (response.data.user.role_id == 6) {
                window.location.href = '/stock/dashboard';
            } else if (response.data.user.role_id == 7) {
                window.location.href = '/tablemanager/dashboard';
            }
        })
        .catch((error) => {
            errorMessage.value = error.response?.data?.message || 'Credenciais inválidas.';
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: errorMessage.value,
                life: 3000
            });
        })
        .finally(() => {
            submitted.value = false;
        });
};

onBeforeMount(() => {
    const token = localStorage.getItem('token');
    if (token) {
        // User is authenticated, proceed to the route
    }
});
</script>

<template>
    <FloatingConfigurator />

    <div class="login-page">
        <div class="login-page__backdrop" aria-hidden="true" />

        <div class="login-shell">
            <section class="login-brand">
                <div class="login-brand__badge">
                    <i class="pi pi-building" />
                    <span>Gestão integrada</span>
                </div>

                <h1>Liv Beira</h1>
                <p class="login-brand__lead">
                    PDV, stock, mesas e relatórios num só lugar. Entre para continuar a operação.
                </p>

                <ul class="login-brand__features">
                    <li>
                        <i class="pi pi-shopping-cart" />
                        Ponto de venda rápido
                    </li>
                    <li>
                        <i class="pi pi-table" />
                        Controlo de mesas em tempo real
                    </li>
                    <li>
                        <i class="pi pi-chart-line" />
                        Caixa e relatórios diários
                    </li>
                </ul>
            </section>

            <section class="login-card">
                <div class="login-card__header">
                    <div class="login-card__logo" aria-hidden="true">
                        <svg viewBox="0 0 54 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M17.1637 19.2467C17.1566 19.4033 17.1529 19.561 17.1529 19.7194C17.1529 25.3503 21.7203 29.915 27.3546 29.915C32.9887 29.915 37.5561 25.3503 37.5561 19.7194C37.5561 19.5572 37.5524 19.3959 37.5449 19.2355C38.5617 19.0801 39.5759 18.9013 40.5867 18.6994L40.6926 18.6782C40.7191 19.0218 40.7326 19.369 40.7326 19.7194C40.7326 27.1036 34.743 33.0896 27.3546 33.0896C19.966 33.0896 13.9765 27.1036 13.9765 19.7194C13.9765 19.374 13.9896 19.0316 14.0154 18.6927L14.0486 18.6994C15.0837 18.9062 16.1223 19.0886 17.1637 19.2467ZM33.3284 11.4538C31.6493 10.2396 29.5855 9.52381 27.3546 9.52381C25.1195 9.52381 23.0524 10.2421 21.3717 11.4603C20.0078 11.3232 18.6475 11.1387 17.2933 10.907C19.7453 8.11308 23.3438 6.34921 27.3546 6.34921C31.36 6.34921 34.9543 8.10844 37.4061 10.896C36.0521 11.1292 34.692 11.3152 33.3284 11.4538ZM43.826 18.0518C43.881 18.6003 43.9091 19.1566 43.9091 19.7194C43.9091 28.8568 36.4973 36.2642 27.3546 36.2642C18.2117 36.2642 10.8 28.8568 10.8 19.7194C10.8 19.1615 10.8276 18.61 10.8816 18.0663L7.75383 17.4411C7.66775 18.1886 7.62354 18.9488 7.62354 19.7194C7.62354 30.6102 16.4574 39.4388 27.3546 39.4388C38.2517 39.4388 47.0855 30.6102 47.0855 19.7194C47.0855 18.9439 47.0407 18.1789 46.9536 17.4267L43.826 18.0518ZM44.2613 9.54743L40.9084 10.2176C37.9134 5.95821 32.9593 3.1746 27.3546 3.1746C21.7442 3.1746 16.7856 5.96385 13.7915 10.2305L10.4399 9.56057C13.892 3.83178 20.1756 0 27.3546 0C34.5281 0 40.8075 3.82591 44.2613 9.54743Z"
                                fill="var(--primary-color)"
                            />
                        </svg>
                    </div>
                    <div>
                        <h2>Bem-vindo de volta</h2>
                        <p>Inicie sessão com as suas credenciais</p>
                    </div>
                </div>

                <div v-if="errorMessage" class="login-error" role="alert">
                    <i class="pi pi-exclamation-circle" />
                    <span>{{ errorMessage }}</span>
                </div>

                <form class="login-form" @submit.prevent="loginUser">
                    <div class="login-field">
                        <label for="email1">Email</label>
                        <span class="login-input-wrap">
                            <i class="pi pi-envelope" />
                            <InputText
                                id="email1"
                                v-model="email"
                                type="email"
                                placeholder="seu@email.com"
                                class="login-input"
                                autocomplete="username"
                                :disabled="submitted"
                            />
                        </span>
                    </div>

                    <div class="login-field">
                        <label for="password1">Palavra-passe</label>
                        <Password
                            id="password1"
                            v-model="password"
                            placeholder="••••••••"
                            :toggleMask="true"
                            :feedback="false"
                            fluid
                            class="login-password"
                            inputClass="login-input login-input--password"
                            :disabled="submitted"
                            autocomplete="current-password"
                        />
                    </div>

                    <div class="login-options">
                        <label class="login-remember" for="rememberme1">
                            <Checkbox v-model="checked" inputId="rememberme1" binary />
                            <span>Lembrar-me</span>
                        </label>
                        <button type="button" class="login-forgot">Esqueceu a palavra-passe?</button>
                    </div>

                    <Button
                        type="submit"
                        label="Entrar"
                        icon="pi pi-sign-in"
                        class="login-submit"
                        :loading="submitted"
                        :disabled="submitted"
                    />
                </form>

                <p class="login-footer">© Liv Beira · Sistema de gestão</p>
            </section>
        </div>
    </div>
</template>

<style scoped>
.login-page {
    --login-border: color-mix(in srgb, var(--surface-border) 70%, var(--text-color) 30%);
    --login-border-soft: color-mix(in srgb, var(--surface-border) 85%, transparent);
    --login-panel: var(--surface-card);
    --login-muted: color-mix(in srgb, var(--surface-ground) 75%, var(--text-color) 5%);
    --login-shadow: 0 20px 50px rgba(15, 23, 42, 0.12), 0 0 0 1px var(--login-border-soft);

    position: relative;
    min-height: 100vh;
    min-height: 100dvh;
    width: 100%;
    display: grid;
    place-items: center;
    padding: 1rem;
    overflow: hidden;
    background: var(--surface-ground);
}

.login-page__backdrop {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 15% 20%, color-mix(in srgb, var(--primary-color) 18%, transparent), transparent 38%),
        radial-gradient(circle at 85% 80%, color-mix(in srgb, var(--primary-color) 12%, transparent), transparent 42%),
        linear-gradient(160deg, var(--surface-ground), color-mix(in srgb, var(--surface-ground) 70%, var(--primary-color) 8%));
    pointer-events: none;
}

.login-shell {
    position: relative;
    z-index: 1;
    width: min(100%, 980px);
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-radius: 1.35rem;
    overflow: hidden;
    border: 1px solid var(--login-border);
    box-shadow: var(--login-shadow);
    background: var(--login-panel);
}

.login-brand {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1.25rem;
    padding: clamp(1.5rem, 4vw, 3rem);
    color: #fff;
    background:
        linear-gradient(145deg, color-mix(in srgb, var(--primary-color) 88%, #000 12%), var(--primary-color)),
        var(--primary-color);
}

.login-brand__badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    width: fit-content;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    background: rgba(255, 255, 255, 0.14);
    border: 1px solid rgba(255, 255, 255, 0.22);
}

.login-brand h1 {
    margin: 0;
    font-size: clamp(2rem, 4vw, 2.75rem);
    line-height: 1.05;
    letter-spacing: -0.03em;
}

.login-brand__lead {
    margin: 0;
    max-width: 28ch;
    font-size: 0.98rem;
    line-height: 1.55;
    color: rgba(255, 255, 255, 0.9);
}

.login-brand__features {
    margin: 0.5rem 0 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.65rem;
}

.login-brand__features li {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.92);
}

.login-brand__features i {
    width: 1.75rem;
    height: 1.75rem;
    display: grid;
    place-items: center;
    border-radius: 0.5rem;
    background: rgba(255, 255, 255, 0.14);
    font-size: 0.85rem;
}

.login-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1.25rem;
    padding: clamp(1.5rem, 4vw, 3rem);
    background: var(--login-panel);
}

.login-card__header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.login-card__logo {
    width: 3.25rem;
    height: 3.25rem;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    border-radius: 0.9rem;
    background: color-mix(in srgb, var(--primary-color) 10%, var(--login-panel));
    border: 1px solid var(--login-border-soft);
}

.login-card__logo svg {
    width: 2rem;
    height: auto;
}

.login-card__header h2 {
    margin: 0;
    font-size: 1.45rem;
    letter-spacing: -0.02em;
}

.login-card__header p {
    margin: 0.2rem 0 0;
    color: var(--text-color-secondary);
    font-size: 0.9rem;
}

.login-error {
    display: flex;
    align-items: flex-start;
    gap: 0.55rem;
    padding: 0.75rem 0.85rem;
    border-radius: 0.75rem;
    border: 1px solid color-mix(in srgb, #ef4444 35%, var(--login-border-soft));
    background: color-mix(in srgb, #ef4444 8%, var(--login-panel));
    color: color-mix(in srgb, #ef4444 75%, var(--text-color));
    font-size: 0.88rem;
}

.login-error i {
    margin-top: 0.1rem;
}

.login-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.login-field {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.login-field label {
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--text-color);
}

.login-input-wrap {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0 0.85rem;
    border-radius: 0.8rem;
    border: 1px solid var(--login-border-soft);
    background: var(--login-muted);
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
}

.login-input-wrap i {
    color: var(--text-color-secondary);
    font-size: 0.9rem;
}

.login-input-wrap :deep(.login-input) {
    width: 100%;
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    padding-left: 0 !important;
}

.login-password :deep(.p-password-input) {
    width: 100%;
    border-radius: 0.8rem;
    border: 1px solid var(--login-border-soft) !important;
    background: var(--login-muted) !important;
    padding: 0.75rem 0.85rem !important;
    box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.03);
}

.login-options {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    flex-wrap: wrap;
    margin-top: 0.15rem;
}

.login-remember {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    cursor: pointer;
    font-size: 0.88rem;
    color: var(--text-color-secondary);
    user-select: none;
}

.login-forgot {
    border: none;
    background: transparent;
    padding: 0;
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--primary-color);
    cursor: pointer;
}

.login-submit {
    width: 100%;
    justify-content: center;
    margin-top: 0.35rem;
    font-weight: 700 !important;
    padding: 0.85rem 1rem !important;
}

.login-footer {
    margin: 0.5rem 0 0;
    text-align: center;
    font-size: 0.78rem;
    color: var(--text-color-secondary);
}

@media (max-width: 860px) {
    .login-shell {
        grid-template-columns: 1fr;
        max-width: 28rem;
    }

    .login-brand {
        padding: 1.5rem 1.35rem 1.25rem;
        gap: 0.85rem;
    }

    .login-brand__lead,
    .login-brand__features {
        display: none;
    }

    .login-brand h1 {
        font-size: 1.75rem;
    }

    .login-card {
        padding: 1.35rem 1.25rem 1.5rem;
    }
}

@media (max-width: 420px) {
    .login-page {
        padding: 0.65rem;
    }

    .login-shell {
        border-radius: 1rem;
    }

    .login-card__header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    .login-options {
        flex-direction: column;
        align-items: flex-start;
    }
}

:deep(.p-password .p-password-toggle-mask-icon) {
    color: var(--text-color-secondary);
}
</style>

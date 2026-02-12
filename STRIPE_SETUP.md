# Configuration Stripe

## Installation

Le package Stripe est déjà installé via Composer :

```bash
composer require stripe/stripe-php
```

## Configuration

### 1. Obtenir les clés Stripe

1. Créez un compte sur [Stripe Dashboard](https://dashboard.stripe.com/)
2. Allez dans **Developers > API keys**
3. Copiez votre **Publishable key** et votre **Secret key**
4. Pour les webhooks, créez un endpoint dans **Developers > Webhooks**

### 2. Configurer les variables d'environnement

Ajoutez ces variables dans votre fichier `.env` :

```env
STRIPE_KEY=pk_test_YOUR_KEY_HERE
STRIPE_SECRET=sk_test_YOUR_SECRET_HERE
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET_HERE
```

### 3. Configurer le webhook Stripe

1. Dans le Dashboard Stripe, allez dans **Developers > Webhooks**
2. Cliquez sur **Add endpoint**
3. URL du endpoint : `https://votre-domaine.com/webhook/stripe`
4. Sélectionnez les événements à écouter :
   - `checkout.session.completed`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
5. Copiez le **Signing secret** (commence par `whsec_`) et ajoutez-le dans `.env` comme `STRIPE_WEBHOOK_SECRET`

### 4. Mode Test vs Production

- **Mode Test** : Utilisez les clés commençant par `pk_test_` et `sk_test_`
- **Mode Production** : Utilisez les clés commençant par `pk_live_` et `sk_live_`

## Fonctionnement

### Processus d'achat

1. L'utilisateur clique sur "Acheter" sur un morceau
2. Une session Stripe Checkout est créée
3. L'utilisateur est redirigé vers Stripe pour le paiement
4. Après paiement, Stripe envoie un webhook à l'application
5. L'achat est marqué comme "completed"
6. Un paiement automatique est créé pour l'artiste (70% du montant)

### Distribution des paiements

- **70%** pour l'artiste
- **30%** de commission pour la plateforme

Les paiements aux artistes sont créés avec le statut `pending`. Vous pouvez ensuite les traiter manuellement via le panneau d'administration ou automatiser avec Stripe Connect (pour une intégration complète).

## Test avec des cartes Stripe

Utilisez ces cartes de test dans le mode Test :

- **Succès** : `4242 4242 4242 4242`
- **Échec** : `4000 0000 0000 0002`
- **3D Secure** : `4000 0025 0000 3155`

Date d'expiration : n'importe quelle date future (ex: 12/34)
CVC : n'importe quel code à 3 chiffres (ex: 123)

## Pour plus d'informations

- [Documentation Stripe](https://stripe.com/docs)
- [Stripe Checkout](https://stripe.com/docs/payments/checkout)
- [Stripe Webhooks](https://stripe.com/docs/webhooks)

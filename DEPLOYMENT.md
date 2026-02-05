# Guide de Déploiement - Music Marketplace

## 🚀 Étapes de déploiement

### 1. Configuration AWS S3
1. Créez un compte AWS : https://aws.amazon.com/
2. Créez un bucket S3 pour stocker les fichiers audio
3. **IMPORTANT - Configuration CORS :**
   ```json
   [
     {
       "AllowedHeaders": ["*"],
       "AllowedMethods": ["GET", "PUT", "POST", "DELETE"],
       "AllowedOrigins": ["https://votre-domaine.com"],
       "ExposeHeaders": ["ETag"]
     }
   ]
   ```
4. Créez un utilisateur IAM avec accès S3
5. Récupérez les clés d'accès (Access Key ID + Secret Access Key)

### 2. Configuration Stripe
1. Créez un compte Stripe : https://stripe.com/
2. Activez votre compte (vérification d'identité)
3. Récupérez vos clés LIVE (pk_live_... et sk_live_...)
4. **CRITIQUE - Configuration Webhook :**
   - URL : `https://votre-domaine.com/webhook/stripe`
   - Événements à écouter :
     - `checkout.session.completed`
     - `payment_intent.succeeded`
     - `payment_intent.payment_failed`
   - Récupérez la clé de signature `whsec_...`

### 3. Configuration de production
1. Copiez `.env.production` vers `.env`
2. Remplacez TOUTES les valeurs par les vraies :
   - `APP_URL` : votre domaine HTTPS
   - `DB_*` : votre base de données MySQL
   - `AWS_*` : vos clés AWS S3
   - `STRIPE_*` : vos clés Stripe LIVE + webhook
   - `MAIL_*` : votre service email

### 4. Déploiement
```bash
# Installer les dépendances
composer install --no-dev --optimize-autoloader

# Générer la clé d'application
php artisan key:generate

# Migrer la base de données
php artisan migrate --force

# Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Créer le lien de stockage
php artisan storage:link
```

### 5. Sécurité
- **HTTPS obligatoire** (certificat SSL/TLS)
- Configurez un firewall
- Sauvegardez régulièrement la base de données
- Surveillez les logs d'erreur
- **Vérifiez les CORS S3** avant le lancement

## ⚠️ POINTS CRITIQUES

### CORS S3
- Le bucket S3 DOIT autoriser les requêtes de votre domaine
- Testez l'upload depuis votre domaine de production
- Vérifiez que les fichiers audio sont accessibles

### Webhooks Stripe
- L'URL webhook DOIT être accessible en HTTPS
- Testez les paiements en mode live
- Vérifiez que les confirmations arrivent bien
- Surveillez les logs webhook dans Stripe

### Base de données
- Sauvegardez avant la migration
- Testez la connexion MySQL
- Vérifiez les permissions utilisateur

## 🧪 Tests avant lancement
1. ✅ Upload d'une piste (S3)
2. ✅ Lecture audio (CORS)
3. ✅ Paiement test (Stripe)
4. ✅ Webhook reçu (logs)
5. ✅ Téléchargement après achat

## ⚠️ IMPORTANT
- Ne jamais commiter le fichier `.env` avec les vraies clés
- Testez d'abord avec les clés de test Stripe
- Vérifiez que AWS S3 fonctionne avant de passer en production
- **Configurez CORS S3 avant le premier upload**
- **Testez les webhooks Stripe avant le lancement**
# Configuration Supabase

## 1. Créer un projet Supabase
- Aller sur https://supabase.com
- Créer un nouveau projet
- Noter l'URL et les clés API

## 2. Configurer .env
Remplacer dans `.env` :
```
DB_HOST=db.your-project-ref.supabase.co
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=your-supabase-password

SUPABASE_URL=https://your-project-ref.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-role-key
```

## 3. Migrer
```bash
php artisan migrate:fresh --seed
```

## 4. Avantages Supabase
- Base PostgreSQL cloud
- Interface admin intégrée
- API REST automatique
- Stockage de fichiers
- Authentification
- Temps réel
- Gratuit jusqu'à 500MB

## 5. Alternative locale (SQLite)
Pour rester en local, remplacer dans `.env` :
```
DB_CONNECTION=sqlite
DB_DATABASE=c:\Users\boude\PhpstormProjects\musicMarketplace\database\database.sqlite
```
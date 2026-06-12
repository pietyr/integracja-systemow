# Zestawienie ekonomiczne

Projekt zaliczeniowy z przedmiotu Integracja Systemów. Aplikacja łączy dane makroekonomiczne z GUS BDL ze artykułami prasowymi NY Times i prezentuje je wspólnie — m.in. na wykresie z kontekstem newsów dla wybranego roku.

Aplikacja uruchamiana jest wyłącznie przez **Docker**.

## Uruchomienie

Wymagania: Docker, Docker Compose.

```bash
cp .env.example .env
# Uzupełnij w .env: NYTIMES_API_KEY oraz wygeneruj klucze w kontenerze:
docker compose run --rm app php artisan key:generate
docker compose run --rm app php artisan jwt:secret

docker compose up --build -d
```

- Aplikacja: http://localhost:8000  
- MySQL z hosta: port `3308`, baza `integracja`, login/hasło: `integracja`  
- W kontenerze startują: migracje, serwer PHP (`app`) oraz worker kolejki (`worker`)  
- Synchronizacja NY Times wznawia się automatycznie po restarcie kontenera `app`

### Pierwsze pobranie danych

```bash
docker compose exec app php artisan integrations:sync --source=gus
docker compose exec app php artisan integrations:sync --source=nytimes
```

### Przydatne polecenia dockera

```bash
docker compose up -d
docker compose ps          # app + worker + mysql
docker compose logs -f worker   # logi synchronizacji NY Times
docker compose logs -f app
docker compose stop
docker compose down
```

### Status synchronizacji

```bash
docker compose exec app php artisan integrations:status
```

Pokazuje m.in.: liczbę zsynchronizowanych miesięcy NY Times, artykułów w bazie, zadań w kolejce, zakres lat w bazie, zużycie dziennego limitu API i kolejny miesiąc do pobrania.

## Jak działa aplikacja

### Przepływ danych

1. **Pobranie z API** — `integrations:sync` zapisuje dane do MySQL.
2. **Prezentacja** — interfejs Vue czyta wyłącznie z lokalnej bazy (Eloquent).
3. **Eksport / REST** — JSON/XML i API z tokenem JWT.

### Źródło: GUS BDL (`--source=gus`)

- API: `https://bdl.stat.gov.pl/api/v1` (bez klucza)
- 7 wskaźników: wynagrodzenia, świadczenia, inflacja, PKB — konfiguracja w `config/integrations.php`

### Źródło: NY Times Archive (`--source=nytimes`)

- Wymaga `NYTIMES_API_KEY` w `.env`
- Łańcuch w kolejce: od **grudnia 2025** wstecz do **stycznia 2010** (zgodnie ze wskaźnikami GUS)
- Limit: 12 s między miesiącami, max **450 zapytań/dzień** (po przekroczeniu wznowienie następnego dnia)
- Max 30 artykułów/miesiąc (filtrowane słowami kluczowymi)
- Pobrane miesiące w `synced_periods` są pomijane przy ponownym uruchomieniu
- Tryb blokujący: `--blocking`
- Reset zawieszonej kolejki: `--force` (np. po restarcie bez workera)

### Widoki

| Ścieżka | Opis |
|---------|------|
| `/dashboard` | Podsumowanie wskaźników i ostatnie newsy |
| `/porownanie` | Wykres wskaźnika + newsy dla wybranego roku |
| `/wskazniki` | Tabele z filtrami |
| `/aktualnosci` | Lista artykułów |
| `/eksport` | Pobieranie JSON/XML |

### REST API

```
POST /api/auth/login
GET  /api/indicators
GET  /api/indicators/export?format=xml
GET  /api/news
```

## Konfiguracja

- `.env` — klucze (`APP_KEY`, `JWT_SECRET`, `NYTIMES_API_KEY`) oraz ustawienia lokalne
- `docker-compose.yml` — nadpisuje w kontenerze połączenie z bazą (`DB_HOST=mysql` itd.)
- `config/integrations.php` — zmienne GUS, zakres i limity NY Times

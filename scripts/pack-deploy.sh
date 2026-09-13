#!/usr/bin/env bash
#
# Φτιάχνει το πακέτο για ανέβασμα στο cPanel: ../karanikolis-deploy.zip
#
#   bash scripts/pack-deploy.sh
#
# Δύο πράγματα που αξίζει να ξέρεις για τον τρόπο που δουλεύει:
#
# 1) Δεν πειράζει τον φάκελο εργασίας σου. Αντιγράφει ό,τι χρειάζεται σε προσωρινό
#    φάκελο και τρέχει εκεί το composer χωρίς τα εργαλεία ανάπτυξης, οπότε το τοπικό
#    σου vendor/ με το Pest και το Pint μένει ανέπαφο.
#
# 2) Αντιγράφει με **λίστα του τι μπαίνει**, όχι λίστα του τι εξαιρείται. Ένας νέος
#    φάκελος στο μέλλον δεν μπορεί να βρεθεί κατά λάθος στο πακέτο· θα λείπει, και
#    θα φανεί, αντί να ανέβει σιωπηλά κάτι που δεν έπρεπε.
#
set -euo pipefail
cd "$(dirname "$0")/.."

PROJECT="$(pwd)"
OUT="$PROJECT/../karanikolis-deploy.zip"
STAGE="$(mktemp -d)"
trap 'rm -rf "$STAGE"' EXIT

echo "→ 1/5  Χτίσιμο assets (npm run build)…"
npm run build

echo "→ 2/5  Αντιγραφή αρχείων εφαρμογής…"
mkdir -p "$STAGE/app"
cd "$PROJECT"

# Ό,τι τρέχει στον server. Το resources/ το χρειάζεται για τα Blade views.
for path in app bootstrap config database public resources routes artisan composer.json composer.lock; do
    cp -R "$path" "$STAGE/app/"
done

cd "$STAGE/app"

# Οι κρυφές μνήμες της Laravel δείχνουν σε τοπικές διαδρομές· ο φάκελος μένει, το
# περιεχόμενο φεύγει.
find bootstrap/cache -type f ! -name '.gitignore' -delete

# Τα πρωτότυπα jpeg υπάρχουν μόνο για να ξαναφτιαχτούν τα webp. Στον server δεν
# ξαναφτιάχνεται τίποτα — δεν υπάρχει Node.
rm -rf resources/images

# Οι φωτογραφίες που θα ανεβάσει ο ιδιοκτήτης ζουν εδώ, στον server. Ό,τι υπάρχει
# τοπικά είναι δοκιμαστικό και δεν έχει καμία δουλειά στο πακέτο· ο φάκελος όμως
# πρέπει να υπάρχει, γιατί εκεί γράφει η PHP.
rm -rf public/images/uploads
mkdir -p public/images/uploads/articles

# Ο φάκελος storage/ φτιάχνεται από την αρχή, άδειος: τα logs και οι μνήμες της
# ανάπτυξης δεν ανεβαίνουν, αλλά η δομή πρέπει να υπάρχει αλλιώς η Laravel σκάει
# στο πρώτο αίτημα.
for directory in storage/app/private storage/app/public storage/framework/cache/data \
                 storage/framework/sessions storage/framework/views storage/logs; do
    mkdir -p "$directory"
    # Ένας εντελώς άδειος φάκελος είναι εύκολο να χαθεί σε ένα zip ή σε ένα
    # extract· ένα αρχείο μέσα του εγγυάται ότι θα φτάσει στον server.
    printf '*\n!.gitignore\n' > "$directory/.gitignore"
done

echo "→ 3/5  composer install χωρίς εργαλεία ανάπτυξης…"
composer install --no-dev --optimize-autoloader --no-interaction --quiet

echo "→ 4/5  Δημιουργία $(basename "$OUT")…"
rm -f "$OUT"
zip -rqX "$OUT" . -x '.DS_Store' '*/.DS_Store'

echo "→ 5/5  Έλεγχος περιεχομένου…"
# Διαβάζεται μία φορά: το `grep -q` κλείνει τον σωλήνα στο πρώτο ταίριασμα, και με
# `pipefail` αυτό μοιάζει με αποτυχία ακόμα κι όταν το αρχείο βρέθηκε.
LISTING="$(unzip -Z1 "$OUT")"

for forbidden in '.env' 'node_modules/' 'tests/' 'docker/' '.git/' 'phpunit.xml' 'resources/images/'; do
    if grep -q "^${forbidden}" <<<"$LISTING"; then
        echo "   ✗ Το πακέτο περιέχει $forbidden — σταματώ." >&2
        exit 1
    fi
done

for required in 'artisan' 'vendor/autoload.php' 'public/index.php' 'public/build/manifest.json' \
                'public/.htaccess' 'public/css/error.css' 'resources/views/app.blade.php' \
                'resources/views/errors/500.blade.php' 'storage/framework/views/.gitignore' \
                'public/images/uploads/articles/'; do
    if ! grep -qx "$required" <<<"$LISTING"; then
        echo "   ✗ Λείπει από το πακέτο: $required — σταματώ." >&2
        exit 1
    fi
done

echo ""
echo "✓ Έτοιμο: $(cd "$(dirname "$OUT")" && pwd)/$(basename "$OUT")"
echo "  Μέγεθος: $(du -h "$OUT" | cut -f1)   Αρχεία: $(wc -l <<<"$LISTING" | tr -d ' ')"
echo ""
echo "  Τα βήματα στο cPanel είναι στο docs/deployment.md."

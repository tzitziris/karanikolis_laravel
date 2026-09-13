# Ανέβασμα στο cPanel

Ο οδηγός για να πάει το σαιτ από τον υπολογιστή σου στον server. Γραμμένος για
φιλοξενία **χωρίς terminal**: όλα γίνονται από το File Manager και από προσωρινές
εργασίες Cron.

Στον server **δεν υπάρχει Node**. Κάθε αλλαγή σε κώδικα ή σε κείμενο των σελίδων
θέλει `npm run build` τοπικά και νέο ανέβασμα. Τα άρθρα και οι φωτογραφίες τους
είναι το μόνο που αλλάζει από τον πίνακα διαχείρισης.

---

## 1. Τι χρειάζεται ο server

| | |
|---|---|
| PHP | **8.3 ή νεότερη** (cPanel → *MultiPHP Manager*) |
| Επεκτάσεις PHP | `gd` (με webp), `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `tokenizer`, `xml`, `ctype`, `curl`, `dom`, `filter`, `session` |
| Βάση | MariaDB (η 10.11 είναι αυτή που δοκιμάζεται τοπικά) |
| Apache | `mod_rewrite` και `mod_headers` |

Στο cPanel → *MultiPHP INI Editor* βάλε:

```
upload_max_filesize = 10M
post_max_size = 12M
memory_limit = 128M
```

Το `upload_max_filesize` είναι το όριο που βλέπει ο ιδιοκτήτης στη φόρμα άρθρου:
η εφαρμογή διαβάζει τη ρύθμιση του server και το γράφει μόνη της στην οθόνη, οπότε
αν το αλλάξεις εδώ αλλάζει και το μήνυμα.

> Το `gd` πρέπει να υποστηρίζει webp. Αν δεν το υποστηρίζει, **καμία φωτογραφία δεν
> θα ανεβαίνει** και η φόρμα θα το λέει στα ελληνικά. Έλεγχος: cPanel →
> *Select PHP Version* → *Extensions* → `gd` τσεκαρισμένο.

---

## 2. Φτιάξε το πακέτο (στον υπολογιστή σου)

```bash
bash scripts/pack-deploy.sh
```

Φτιάχνει το `../karanikolis-deploy.zip`. Χτίζει τα assets, τραβάει τις βιβλιοθήκες
**χωρίς τα εργαλεία ανάπτυξης**, και ελέγχει το ίδιο του το αποτέλεσμα: αν λείπει
κάτι απαραίτητο ή αν μπήκε μέσα κάτι που δεν έπρεπε, σταματά με μήνυμα.

**Δεν** μπαίνουν στο πακέτο: το `.env`, τα tests, ο φάκελος `docker/`, το
`node_modules/`, το `.git/`, τα πρωτότυπα jpeg από το `resources/images/`, και οι
φωτογραφίες που έχεις ανεβάσει τοπικά.

Ο φάκελος `public/images/uploads/` μπαίνει **άδειος**. Εκεί ζουν οι φωτογραφίες των
άρθρων στον server, και ένα νέο ανέβασμα δεν τις πειράζει: το extract προσθέτει και
αντικαθιστά αρχεία, δεν σβήνει.

---

## 3. Πού πάει

Ο κατάλογος `public/` πρέπει να είναι το **document root**, και τίποτα άλλο να μην
είναι προσβάσιμο από το web.

**Ο σωστός τρόπος** (subdomain ή addon domain): cPanel → *Domains* → όρισε
document root `/home/USER/karanikolis/public` και ανέβασε το zip στο
`/home/USER/karanikolis/`.

**Αν το cPanel δεν σε αφήνει** να αλλάξεις το document root του κύριου domain:

1. Ανέβασε και κάνε extract το zip στο `/home/USER/karanikolis/`.
2. Μετακίνησε **το περιεχόμενο** του `/home/USER/karanikolis/public/` μέσα στο
   `/home/USER/public_html/` (μαζί με το `.htaccess` — το File Manager κρύβει τα
   κρυφά αρχεία, βάλε *Settings → Show Hidden Files*).
3. Άνοιξε το `/home/USER/public_html/index.php` και άλλαξε **δύο γραμμές**:

   ```php
   require __DIR__.'/../karanikolis/vendor/autoload.php';
   $app = require_once __DIR__.'/../karanikolis/bootstrap/app.php';
   ```

   και τη γραμμή του maintenance ακριβώς από πάνω:

   ```php
   if (file_exists($maintenance = __DIR__.'/../karanikolis/storage/framework/maintenance.php')) {
   ```

Και στις δύο περιπτώσεις: **σβήσε το zip από τον server** μετά το extract.

---

## 4. Το `.env` στον server

Δεν υπάρχει στο πακέτο, και δεν πρέπει να μπει ποτέ. Φτιάξ' το με το File Manager
στη ρίζα της εφαρμογής (`/home/USER/karanikolis/.env`), δίπλα στο `artisan` — **όχι**
μέσα στο `public/`.

```ini
APP_NAME=Karanikolis
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://to-domain-sou.gr

APP_LOCALE=el
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=el_GR

LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=xxx_karanikolis
DB_USERNAME=xxx_karanikolis
DB_PASSWORD=ο-κωδικός-της-βάσης

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local
BROADCAST_CONNECTION=log

MAIL_MAILER=log
```

Τρία σημεία που αξίζουν προσοχή:

- **`APP_DEBUG=false`.** Με `true`, ένα σφάλμα δείχνει στον επισκέπτη ολόκληρο το
  stack trace με διαδρομές και ρυθμίσεις — και οι ελληνικές σελίδες σφάλματος δεν
  εμφανίζονται καθόλου.
- **`APP_KEY` κενό προς το παρόν.** Θα το γράψει η εντολή του επόμενου βήματος.
- **`DB_DATABASE` / `DB_USERNAME`** έχουν στο cPanel πρόθεμα με το όνομα του
  λογαριασμού. Φτιάξε βάση και χρήστη από *MySQL® Databases*, δώσε στον χρήστη
  **ALL PRIVILEGES** πάνω στη βάση, και αντίγραψε τα ονόματα όπως ακριβώς τα δείχνει.

Το `APP_NAME` δεν φαίνεται πουθενά στο σαιτ· το όνομα «Μαχητές Ελευθερούπολης» ζει
μέσα στον κώδικα.

---

## 5. Δικαιώματα

| φάκελος/αρχείο | δικαιώματα |
|---|---|
| φάκελοι γενικά | `755` |
| αρχεία γενικά | `644` |
| `.env` | `600` |
| `storage/` και όλα από κάτω | `755` (πρέπει να είναι εγγράψιμα) |
| `bootstrap/cache/` | `755` (εγγράψιμο) |
| `public/images/uploads/` | `755` (εγγράψιμο) |

Στο cPanel τα αρχεία ανήκουν ήδη στον χρήστη που τρέχει η PHP, οπότε το `755`
αρκεί· δεν χρειάζεται ποτέ `777`.

---

## 6. Οι τρεις προσωρινές εργασίες Cron

Δεν υπάρχει terminal, οπότε οι εντολές που τρέχουν **μία φορά** μπαίνουν ως Cron,
τρέχουν, και μετά **σβήνονται**. cPanel → *Cron Jobs*.

Βάλε ώρα λίγα λεπτά μπροστά, περίμενε να τρέξει, δες το αποτέλεσμα στο email που
στέλνει το cPanel, και **σβήσε την εργασία**.

Η διαδρομή της PHP στο cPanel είναι συνήθως `/usr/local/bin/php`. Αν δεν δουλέψει,
δες στο *Select PHP Version* ποια έκδοση είναι ενεργή και χρησιμοποίησε
`/opt/cpanel/ea-php83/root/usr/bin/php`.

**α) Το κλειδί της εφαρμογής** — μία φορά, στην πρώτη εγκατάσταση:

```
/usr/local/bin/php /home/USER/karanikolis/artisan key:generate --force
```

Γράφει το `APP_KEY` μέσα στο `.env`. **Μην το ξανατρέξεις αργότερα**: αλλάζοντας το
κλειδί, όλες οι ενεργές συνδέσεις σπάνε.

**β) Οι πίνακες της βάσης** — στην πρώτη εγκατάσταση, και ξανά κάθε φορά που ένα νέο
ανέβασμα φέρνει migration:

```
/usr/local/bin/php /home/USER/karanikolis/artisan migrate --force
```

**γ) Ο λογαριασμός διαχειριστή** — μία φορά:

Ο κωδικός **δεν μπαίνει στη γραμμή της εντολής**· η γραμμή εντολών φαίνεται στη
λίστα των cron και συχνά καταγράφεται. Αντί γι' αυτό:

1. Φτιάξε με το File Manager ένα αρχείο `/home/USER/kodikos.txt` που να έχει μέσα
   **μόνο τον κωδικό**, σε μία γραμμή. Τουλάχιστον **10 χαρακτήρες**.
2. Βάλε την εργασία:

   ```
   /usr/local/bin/php /home/USER/karanikolis/artisan admin:create --email=onoma@to-domain-sou.gr --password-file=/home/USER/kodikos.txt
   ```

3. Η εντολή **σβήνει μόνη της το αρχείο** μόλις το διαβάσει. Επιβεβαίωσε ότι έφυγε.
4. Σβήσε την εργασία.

Η ίδια εντολή αλλάζει και τον κωδικό ενός υπάρχοντος διαχειριστή — τρέξ' την ξανά με
το ίδιο email.

Δεν χρειάζεται καμία **μόνιμη** εργασία Cron. Το σαιτ δεν έχει ουρές ούτε
προγραμματισμένες δουλειές.

---

## 7. Έλεγχος μετά το ανέβασμα

Με τη σειρά, γιατί το καθένα αποκλείει μια διαφορετική αιτία:

1. `https://to-domain-sou.gr/` → η αρχική, με φωτογραφίες.
2. `https://to-domain-sou.gr/news` → το αρχείο ειδήσεων με τα άρθρα.
3. `https://to-domain-sou.gr/robots.txt` → πρέπει να λέει `Disallow: /admin` και να
   δίνει τη διεύθυνση του sitemap **με το πραγματικό σου domain**.
4. `https://to-domain-sou.gr/sitemap.xml` → κατάλογος με τις σελίδες και τα άρθρα.
5. `https://to-domain-sou.gr/mia-anyparkti-selida` → η ελληνική σελίδα «Η σελίδα δεν
   βρέθηκε». Αν δεις αγγλικά ή stack trace, το `APP_DEBUG` δεν είναι `false`.
6. `https://to-domain-sou.gr/admin/login` → σύνδεση με τα στοιχεία του βήματος 6γ.
7. Μέσα στον πίνακα: **ανέβασε μια φωτογραφία** σε ένα άρθρο. Αυτό ελέγχει μαζί το
   `gd` με webp, τα δικαιώματα του `public/images/uploads/`, και τα όρια της PHP.
8. Δημοσίευσε ένα άρθρο και **μοίρασέ το στο Facebook**: πρέπει να βγει με τίτλο,
   περιγραφή και εικόνα. Αν είχες μοιράσει τον σύνδεσμο πριν, το Facebook κρατά ό,τι
   είδε την πρώτη φορά — καθάρισέ το από το *Sharing Debugger* του Facebook.

Αν κάτι δεν δουλεύει, η αιτία γράφεται στο `storage/logs/laravel.log`.

---

## 8. Επόμενα ανεβάσματα

Το ίδιο πράγμα κάθε φορά, χωρίς να χρειάζεται να σκέφτεσαι τι άλλαξε:

1. `bash scripts/pack-deploy.sh`
2. Ανέβασε και κάνε extract **πάνω από τα υπάρχοντα**, με *Overwrite*.
3. Σβήσε το zip από τον server.
4. **Μόνο αν** το ανέβασμα έφερε migration: τρέξε ξανά την προσωρινή εργασία (β).

Το `.env` δεν είναι στο πακέτο, οπότε δεν κινδυνεύει. Οι ανεβασμένες φωτογραφίες
ούτε αυτές: το πακέτο έχει τον φάκελό τους άδειο και το extract δεν σβήνει τίποτα.

Αν κάτι πάει στραβά, το προηγούμενο zip είναι όλη η προηγούμενη έκδοση — κάνε extract
εκείνο.

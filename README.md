### Rularea proiectului

Porniți serverul PHP integrat din directorul rădăcinii repository-ului:

```powershell
C:\xampp\php\php.exe -S localhost:8000 router.php
```

Deschideți `http://localhost:8000`. Routerul blochează accesul prin browser la fișierele `.env` și `.git`.

### MySQL și PDO

1. În XAMPP activați `extension=pdo_mysql` în `php.ini` și reporniți Apache sau serverul PHP.
2. În MySQL Workbench selectați schema `uleiuri-vegetalephp`.
3. Deschideți `site/database/schema.sql` în Workbench, selectați schema și rulați scriptul. Acesta creează `users`, `categories`, `items`, `requests` și `request_items` cu chei străine și reguli de păstrare a istoricului. Recomand MySQL 8.0.16 sau mai nou pentru aplicarea constrângerilor `CHECK`.
   Dacă baza de date exista deja, rulați o singură dată `site/database/migration_add_item_image.sql` pentru coloana opțională a imaginilor. Migrarea a fost aplicată bazei conectate acestui proiect.
4. `site/database/seed.sql` adaugă datele demonstrative pentru laborator. Rulați-l o singură dată doar dacă tabelele sunt goale; conturile de test au parola `Lab4Demo2026!`.
5. În `.env` din rădăcina proiectului setați hostul, portul, schema `uleiuri-vegetalephp`, utilizatorul și parola MySQL. Pentru MySQL online folosiți hostul și credentialele date de furnizor.
6. Porniți serverul din rădăcina proiectului. Fișierul `.env` este ignorat de Git și blocat la acces direct prin browser.

Conexiunea PDO este definită în `site/components/database.php`. Pagina `site/pages/produse.php` citește produsele active din `items` și categoriile din `categories`; pentru afișare, tabelele trebuie să conțină date.

Autentificarea folosește sesiuni și verifică parolele cu `password_verify()`. Conturile de test și parola lor sunt listate pe pagina de autentificare. Rolurile nu mai sunt citite din URL.

Formularul de comandă creează solicitări în `requests` și detalii în `request_items`; pagina „Contul meu” afișează numai solicitările utilizatorului autentificat.

Pagina `site/pages/administrare.php` permite administratorului să adauge și să modifice categorii și produse, să încarce imagini JPG/PNG/WebP de maximum 2 MB, să activeze/dezactiveze produse și să le șteargă numai dacă nu apar în `request_items`. Imaginile se păstrează în `site/img/products`, iar pagina `site/pages/produs.php` afișează detaliile produsului și trimite spre formularul de comandă. Lista categoriilor folosește `LEFT JOIN` și `COUNT()` pentru numărul produselor. Formularele folosesc POST, token CSRF și redirecționare după salvare.

Pictograma din dreapta sus deschide alegerea dintre logare și sign up. Deconectarea se face din profil. Paginile Comandă și Contul meu cer o sesiune client/administrator, iar Administrare cere rol de administrator; accesul direct fără drepturi primește răspuns 404.

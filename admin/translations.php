<?php
/**
 * Admin - Translations Management
 */
$pageTitle = 'Prijevodi';
require_once 'includes/header.php';

$message = '';
$error = '';

// Note: Translations are currently managed in script.js
// This page provides info on how to modify them
?>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Upravljanje prijevodima</h2>
    </div>
    
    <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px;">
        Prijevodi se trenutno nalaze u datoteci <code>script.js</code> unutar objekta <code>translations</code>.
        Stranica podržava dva jezika: <strong>Hrvatski (hr)</strong> i <strong>Engleski (en)</strong>.
    </p>
    
    <h3 style="margin-bottom: 12px; font-size: 16px;">Kako urediti prijevode:</h3>
    
    <ol style="color: var(--text-secondary); margin-left: 20px; line-height: 2;">
        <li>Otvorite datoteku <code>script.js</code></li>
        <li>Pronađite sekciju <code>const translations = { ... }</code></li>
        <li>Unutar <code>hr: { ... }</code> su hrvatski prijevodi</li>
        <li>Unutar <code>en: { ... }</code> su engleski prijevodi</li>
        <li>Izmijenite željene tekstove i spremite datoteku</li>
    </ol>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Primjer strukture prijevoda</h2>
    </div>
    
    <pre style="background: var(--bg-input); padding: 20px; border-radius: 8px; overflow-x: auto; font-size: 13px; line-height: 1.6;">
<code>const translations = {
  hr: {
    'nav-home': 'Početna',
    'nav-packages': 'Paketi',
    'nav-contact': 'Kontakt',
    'hero-title-1': 'Profesionalni',
    'hero-title-2': 'Web Dizajn',
    // ... ostali prijevodi
  },
  en: {
    'nav-home': 'Home',
    'nav-packages': 'Packages',
    'nav-contact': 'Contact',
    'hero-title-1': 'Professional',
    'hero-title-2': 'Web Design',
    // ... ostali prijevodi
  }
};</code></pre>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Kategorije prijevoda</h2>
    </div>
    
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Kategorija</th>
                    <th>Prefiks ključa</th>
                    <th>Opis</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Navigacija</strong></td>
                    <td><code>nav-*</code></td>
                    <td>Linkovi u navigaciji</td>
                </tr>
                <tr>
                    <td><strong>Hero sekcija</strong></td>
                    <td><code>hero-*</code></td>
                    <td>Naslovi i tekst na početnoj sekciji</td>
                </tr>
                <tr>
                    <td><strong>Paketi</strong></td>
                    <td><code>package-*</code>, <code>feature-*</code></td>
                    <td>Nazivi paketa, značajke, cijene</td>
                </tr>
                <tr>
                    <td><strong>Kontakt</strong></td>
                    <td><code>contact-*</code></td>
                    <td>Kontakt forma i informacije</td>
                </tr>
                <tr>
                    <td><strong>FAQ</strong></td>
                    <td><code>faq-*</code></td>
                    <td>Pitanja i odgovori</td>
                </tr>
                <tr>
                    <td><strong>Footer</strong></td>
                    <td><code>footer-*</code></td>
                    <td>Tekstovi u podnožju</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card__header">
        <h2 class="card__title">Buduća nadogradnja</h2>
    </div>
    
    <p style="color: var(--text-secondary); line-height: 1.6;">
        U budućoj verziji, prijevodi će se moći uređivati direktno kroz admin panel 
        i spremati u bazu podataka. Za sada, ručno uređivanje <code>script.js</code> 
        datoteke je najpouzdaniji način za izmjenu tekstova.
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>


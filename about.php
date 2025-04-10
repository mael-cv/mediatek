<?php
include_once "./utils/config.php";
include_once "./partials/top.php";
?>

<div class="container">
    <h2>À propos du projet MediaTek</h2>
    
    <div class="about-section">
        <h3>Présentation du projet</h3>
        <p>
            MediaTek est une bibliothèque numérique développée dans le cadre d'un projet académique OWASP 2024-2025.
            Ce projet vise à créer une plateforme de gestion de bibliothèque sécurisée, en appliquant les meilleures 
            pratiques de développement web et en respectant les recommandations de l'OWASP (Open Web Application Security Project).
        </p>
        <p>
            L'objectif principal est de fournir un système complet permettant la gestion d'un catalogue de livres dans une bibliothèque, librairie ou plateforme d'emprunt en ligne,
            avec des fonctionnalités de consultation, de découverte aléatoire, d'emprunt et une interface d'administration sécurisée.
        </p>
    </div>
    
    <div class="about-section">
        <h3>Technologies utilisées</h3>
        <ul>
            <li><strong>Backend :</strong> PHP 8 avec architecture MVC</li>
            <li><strong>Base de données :</strong> MySQL avec PDO pour des requêtes sécurisées</li>
            <li><strong>Frontend :</strong> HTML5, CSS3, JavaScript</li>
            <li><strong>Framework CSS :</strong> PicoCSS pour une interface responsive et minimaliste</li>
            <li><strong>Iconographie :</strong> Light Icons pour une interface utilisateur intuitive</li>
            <li><strong>Sécurité :</strong> 
                <ul>
                    <li>Protection contre les injections SQL</li>
                    <li>Hachage sécurisé des mots de passe</li>
                    <li>Protection CSRF</li>
                    <li>Validation des entrées utilisateur</li>
                    <li>Gestion des sessions sécurisée</li>
                    <li>Google reCAPTCHA pour la protection des formulaires</li>
                </ul>
            </li>
        </ul>
    </div>
    
    <div class="about-section">
        <h3>Fonctionnalités</h3>
        <ul>
            <li>Catalogue complet de livres avec emprunt</li>
            <li>Découverte de livres aléatoires</li>
            <li>Interface d'administration pour la gestion du contenu</li>
            <li>Système d'authentification sécurisé</li>
            <li>Design responsive adapté à tous les appareils</li>
        </ul>
    </div>
    
    <div class="about-section github-section" style="text-align: center; margin-top: 2rem;">
        <h3>Code source</h3>
        <p>Ce projet est disponible sur GitHub. Consultez le code source pour plus de détails sur l'implémentation.</p>
        <a href="https://github.com/mael-cv/mediatek" class="github-button" role="button" style="display: inline-block; padding: 0.75rem 1.5rem; background: #333; color: white; text-decoration: none; border-radius: 4px; margin-top: 1rem;">
            <i class="light-icon-github" style="margin-right: 0.5rem;"></i> Voir sur GitHub
        </a>
    </div>
    
    <div class="about-section">
        <h3>Équipe de développement</h3>
        <p>
            Projet développé par des étudiants dans le cadre du cours OWASP 2024-2025.
        </p>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>
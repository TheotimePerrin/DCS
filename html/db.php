<?php
// =====================================================
// CONFIGURATION DE LA CONNEXION (XAMPP)
// =====================================================

define('DB_HOST',     'localhost');
define('DB_PORT',     3306);
define('DB_NAME',     'campus_it');
define('DB_USER',     'root');       // Utilisateur par défaut XAMPP
define('DB_PASS',     '');           // Mot de passe vide par défaut XAMPP
define('DB_CHARSET',  'utf8mb4');

// =====================================================
// CONNEXION PDO
// =====================================================

function getConnection(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode([
                'error'   => 'Erreur de connexion à la base de données.',
                'details' => $e->getMessage()
            ]));
        }
    }

    return $pdo;
}

// =====================================================
// FONCTIONS UTILITAIRES DE REQUÊTES
// =====================================================

/**
 * Exécute une requête SELECT et retourne tous les résultats.
 *
 * @param string $sql    Requête SQL avec placeholders (? ou :nom)
 * @param array  $params Paramètres à lier
 * @return array
 */
function fetchAll(string $sql, array $params = []): array {
    $stmt = getConnection()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Exécute une requête SELECT et retourne une seule ligne.
 *
 * @param string $sql
 * @param array  $params
 * @return array|false
 */
function fetchOne(string $sql, array $params = []): array|false {
    $stmt = getConnection()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Exécute une requête INSERT / UPDATE / DELETE.
 * Retourne le nombre de lignes affectées.
 *
 * @param string $sql
 * @param array  $params
 * @return int
 */
function execute(string $sql, array $params = []): int {
    $stmt = getConnection()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

/**
 * Retourne le dernier ID inséré (après un INSERT).
 *
 * @return string
 */
function lastInsertId(): string {
    return getConnection()->lastInsertId();
}

// =====================================================
// REQUÊTES MÉTIER — campus_it
// =====================================================

/**
 * Top N applications par consommation totale (toutes ressources confondues).
 *
 * @param int $limit Nombre de résultats souhaités (défaut : 5)
 * @return array
 */
function getTopApplications(int $limit = 5): array {
    $sql = "
        SELECT
            a.app_id,
            a.nom              AS application,
            SUM(c.volume)      AS total_consommation
        FROM consommation c
        JOIN application  a ON a.app_id = c.app_id
        GROUP BY a.app_id, a.nom
        ORDER BY total_consommation DESC
        LIMIT ?
    ";
    return fetchAll($sql, [$limit]);
}

/**
 * Évolution mensuelle de la consommation (toutes applis, toutes ressources).
 *
 * @return array  [ ['mois' => '2024-01-01', 'total' => 1234.56], ... ]
 */
function getEvolutionMensuelle(): array {
    $sql = "
        SELECT
            DATE_FORMAT(mois, '%Y-%m') AS mois,
            SUM(volume)                AS total_consommation
        FROM consommation
        GROUP BY DATE_FORMAT(mois, '%Y-%m')
        ORDER BY mois ASC
    ";
    return fetchAll($sql);
}

/**
 * Comparaison de la consommation par ressource pour une application donnée.
 *
 * @param int $appId Identifiant de l'application
 * @return array
 */
function getConsommationParRessource(int $appId): array {
    $sql = "
        SELECT
            r.nom              AS ressource,
            r.unite,
            SUM(c.volume)      AS total_consommation
        FROM consommation c
        JOIN ressource r ON r.res_id = c.res_id
        WHERE c.app_id = ?
        GROUP BY r.res_id, r.nom, r.unite
        ORDER BY total_consommation DESC
    ";
    return fetchAll($sql, [$appId]);
}

/**
 * Liste de toutes les applications.
 *
 * @return array
 */
function getAllApplications(): array {
    return fetchAll("SELECT app_id, nom FROM application ORDER BY nom ASC");
}

/**
 * Liste de toutes les ressources.
 *
 * @return array
 */
function getAllRessources(): array {
    return fetchAll("SELECT res_id, nom, unite FROM ressource ORDER BY nom ASC");
}

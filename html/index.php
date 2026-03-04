<?php
// db.php est dans le même dossier htdocs/php/
require_once __DIR__ . '/db.php';

$topApps       = getTopApplications(5);
$evolutionMens = getEvolutionMensuelle();

$sqlComparaison = "
    SELECT
        DATE_FORMAT(c.mois, '%Y-%m') AS mois,
        SUM(CASE WHEN r.nom = 'Stockage' THEN c.volume ELSE 0 END) AS stockage,
        SUM(CASE WHEN r.nom = 'Réseau'   THEN c.volume ELSE 0 END) AS reseau
    FROM consommation c
    JOIN ressource r ON r.res_id = c.res_id
    WHERE r.nom IN ('Stockage', 'Réseau')
    GROUP BY DATE_FORMAT(c.mois, '%Y-%m')
    ORDER BY mois ASC
";
$comparaison = fetchAll($sqlComparaison);

$activeTab = $_GET['tab'] ?? 'Tab1';
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Campus IT</title>
    <!-- Depuis htdocs/php/, le CSS est dans htdocs/css/ donc on remonte avec ../ -->
    <link rel="stylesheet" type="text/css" href="../css/style.css" />
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #222; background: #fff; padding: 24px 32px; }
        h1   { font-size: 26px; font-weight: bold; margin-bottom: 4px; }
        .subtitle { font-size: 12px; color: #666; margin-bottom: 20px; }
        .subtitle strong { color: #333; }

        .tab { display: flex; border-bottom: 1px solid #ccc; margin-bottom: 28px; }
        .tablinks { background: #f0f0f0; border: 1px solid #ccc; border-bottom: none; padding: 10px 22px; cursor: pointer; font-size: 13px; color: #333; margin-right: 2px; border-radius: 4px 4px 0 0; }
        .tablinks:hover  { background: #e0e0e0; }
        .tablinks.active { background: #fff; font-weight: bold; border-bottom: 1px solid #fff; margin-bottom: -1px; }

        .tabcontent        { display: none; }
        .tabcontent.active { display: block; }
        .tabcontent h2 { font-size: 17px; font-weight: bold; margin-bottom: 18px; }

        table  { width: 100%; border-collapse: collapse; max-width: 700px; }
        th, td { text-align: left; padding: 10px 16px; border: 1px solid #ccc; font-size: 13px; }
        th     { background: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) td { background: #fafafa; }
        .note  { margin-top: 14px; font-size: 12px; color: #777; font-style: italic; }
    </style>
</head>
<body>

<h1>Tableau de bord - Campus IT</h1>
<p class="subtitle">Données de démonstration — base&nbsp;: <strong>campus_it</strong>.</p>

<div class="tab">
    <button class="tablinks <?= $activeTab === 'Tab1' ? 'active' : '' ?>" onclick="openTab(event, 'Tab1')">Top applications</button>
    <button class="tablinks <?= $activeTab === 'Tab2' ? 'active' : '' ?>" onclick="openTab(event, 'Tab2')">Évolution mensuelle</button>
    <button class="tablinks <?= $activeTab === 'Tab3' ? 'active' : '' ?>" onclick="openTab(event, 'Tab3')">Comparaison ressources</button>
</div>

<!-- TAB 1 — Top 5 applications -->
<div id="Tab1" class="tabcontent <?= $activeTab === 'Tab1' ? 'active' : '' ?>">
    <h2>Top 5 des applications (consommation totale)</h2>
    <table>
        <thead><tr><th>Application</th><th>Total (unités cumulées)</th></tr></thead>
        <tbody>
            <?php if (empty($topApps)): ?>
                <tr><td colspan="2">Aucune donnée disponible.</td></tr>
            <?php else: foreach ($topApps as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['application']) ?></td>
                    <td><?= number_format((float)$row['total_consommation'], 2, ',', '&nbsp;') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- TAB 2 — Évolution mensuelle -->
<div id="Tab2" class="tabcontent <?= $activeTab === 'Tab2' ? 'active' : '' ?>">
    <h2>Évolution mensuelle (total campus)</h2>
    <table>
        <thead><tr><th>Mois</th><th>Total (unités cumulées)</th></tr></thead>
        <tbody>
            <?php if (empty($evolutionMens)): ?>
                <tr><td colspan="2">Aucune donnée disponible.</td></tr>
            <?php else: foreach ($evolutionMens as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['mois']) ?></td>
                    <td><?= number_format((float)$row['total_consommation'], 2, ',', '&nbsp;') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <p class="note">Bonus&nbsp;: transformer ce tableau en graphique (Chart.js) si souhaité.</p>
</div>

<!-- TAB 3 — Comparaison Stockage vs Réseau -->
<div id="Tab3" class="tabcontent <?= $activeTab === 'Tab3' ? 'active' : '' ?>">
    <h2>Comparaison Stockage vs Réseau</h2>
    <table>
        <thead><tr><th>Mois</th><th>Stockage (Go)</th><th>Réseau (Go)</th></tr></thead>
        <tbody>
            <?php if (empty($comparaison)): ?>
                <tr><td colspan="3">Aucune donnée disponible.</td></tr>
            <?php else: foreach ($comparaison as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['mois']) ?></td>
                    <td><?= number_format((float)$row['stockage'], 2, ',', '&nbsp;') ?></td>
                    <td><?= number_format((float)$row['reseau'],   2, ',', '&nbsp;') ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Depuis htdocs/php/, le JS est dans htdocs/js/ donc on remonte avec ../ -->
<script src="../js/script.js"></script>
<script>
function openTab(event, tabName) {
    document.querySelectorAll('.tabcontent').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tablinks').forEach(el  => el.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    event.currentTarget.classList.add('active');
}
</script>

</body>
</html>

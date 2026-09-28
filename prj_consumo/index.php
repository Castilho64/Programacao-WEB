<?php require "consulta.php"; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos - DummyJSON</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main>
        <h1>Produtos - DummyJSON</h1>
        <p class="sub">Consumo da API com PHP + cURL (método GET)</p>

        <form method="get" class="filtros">
            <input type="text" name="q" placeholder="Buscar por termo(ex.: phone)" value="<?= htmlspecialchars($busca) ?>">
            <input type="text" name="id" placeholder="ou ID (ex.: 5)" value="<?= htmlspecialchars($id) ?>">
            <button type="submit">Consultar</button>
            <a href="index.php">Limpar</a>
        </form>

                <!-- Status da requisição -->
        <?php if ($sucesso) { ?>
            <div class="status ok">
                <strong>Sucesso</strong> Requisição executada com sucesso.
                <span class="codigo">HTTP <?= $codigoHttp ?></span>
                <div class="endpoint">GET <?= $url ?></div>
            </div>
        <?php } else { ?>
            <div class="status erro">
                <strong>Erro</strong> <?= $erro ?>
                <?php if ($codigoHttp > 0) { ?>
                    <span class="codigo">HTTP <?= $codigoHttp ?></span>
                <?php } ?>
                <?php if ($url !== "") { ?>
                    <div class="endpoint">GET <?= $url ?></div>
                <?php } ?>
            </div>
        <?php } ?>
 
        <?php if ($sucesso && count($produtos) === 0) { ?>
            <p class="vazio">Nenhum produto encontrado.</p>
        <?php } ?>
 
        <!-- Produtos -->
        <section class="grade">
            <?php foreach ($produtos as $p) { ?>
                <article class="card">
                    <img src="<?= $p["thumbnail"] ?>" alt="<?= $p["title"] ?>">
                    <h2><?= $p["title"] ?></h2>
                    <p class="cat"><?= $p["category"] ?></p>
                    <p class="preco">US$ <?= number_format($p["price"], 2, ",", ".") ?></p>
 
                    <?php if ($id !== "") { ?>
                        <p><?= $p["description"] ?></p>
                        <ul>
                            <li>ID: <?= $p["id"] ?></li>
                            <li>Marca: <?= $p["brand"] ?? "Não informada" ?></li>
                            <li>Avaliação: <?= $p["rating"] ?></li>
                            <li>Estoque: <?= $p["stock"] ?></li>
                        </ul>
                    <?php } else { ?>
                        <a href="?id=<?= $p["id"] ?>">Ver detalhes</a>
                    <?php } ?>
                </article>
            <?php } ?>
        </section>
 
        <!-- Resposta bruta da API -->
        <?php if ($dados !== null) { ?>
            <details>
                <summary>Ver resposta JSON completa</summary>
                <pre><?= htmlspecialchars(json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
            </details>
        <?php } ?>
    </main>

</body>
</html>
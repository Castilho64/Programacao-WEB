# Produtos - DummyJSON (PHP + cURL)

Projeto em PHP que consome a API pública [DummyJSON](https://dummyjson.com/) usando a biblioteca **cURL**, com a operação **GET**. A página lista produtos, permite buscar por termo, mostrar o detalhe de um produto por ID e informa se a requisição deu certo ou se ocorreu algum erro.

Documentação da API: https://dummyjson.com/docs

## Funcionalidades

- **Listagem:** ao abrir a página, mostra 12 produtos em cards (`/products?limit=12`).
- **Busca por termo:** pesquisa produtos pela palavra digitada (`/products/search?q=...`).
- **Detalhe por ID:** mostra descrição, marca, avaliação e estoque de um produto (`/products/{id}`).
- **Status da requisição:** faixa verde (sucesso) ou vermelha (erro), com o código HTTP e a URL chamada.
- **JSON completo:** bloco expansível com a resposta bruta da API.

## Estrutura do projeto

```
prj_php_api/
├── index.php      # HTML da página (formulário, status, cards)
├── consulta.php   # Lógica: monta a URL, faz a requisição cURL e trata erros
├── style.css      # Estilo simples da página
└── README.md
```

O `index.php` começa com `require "consulta.php";`. Assim, as variáveis criadas na lógica (`$sucesso`, `$erro`, `$produtos` etc.) ficam disponíveis para o HTML.

## Como funciona

1. O formulário envia os dados pela URL (método GET): `index.php?q=phone` ou `index.php?id=5`.
2. O `consulta.php` lê esses valores com `$_GET` e escolhe qual endereço da API chamar.
3. O cURL faz a requisição e o `json_decode` transforma a resposta em array.
4. O código verifica se houve erro e guarda o resultado em variáveis.
5. O `index.php` exibe o status e os produtos usando essas variáveis.

## Partes cruciais do código

**1. Escolha do endpoint** (`consulta.php`)

```php
$id = trim($_GET["id"] ?? "");
$busca = trim($_GET["q"] ?? "");

if ($id !== ""){
    if (ctype_digit($id)){
        $url = "https://dummyjson.com/products/$id";
    } else{
        $erro = "O ID precisa ser um número inteiro.";
    }
} elseif ($busca !== ""){
    $url = "https://dummyjson.com/products/search?q=".urlencode($busca)."&limit=12";
} else{
    $url = "https://dummyjson.com/products?limit=12";
}
```

- `trim` remove espaços no começo e no fim do texto digitado.
- `ctype_digit` garante que o ID seja um número inteiro antes de chamar a API.
- `urlencode` converte espaços e caracteres especiais para um formato aceito em URL.

**2. Requisição com cURL**

```php
$curl = curl_init($url);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_TIMEOUT, 10);

$response = curl_exec($curl);
$codigoHttp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
```

- `CURLOPT_RETURNTRANSFER` faz o `curl_exec` devolver a resposta como texto, em vez de imprimir na tela.
- `CURLOPT_TIMEOUT` define 10 segundos como tempo máximo de espera.
- `CURLINFO_HTTP_CODE` pega o código HTTP da resposta (200, 404 etc.).

**3. Tratamento de erros**

```php
if ($response === false){
    $erro = "Falha de conexão: ".curl_error($curl);
} else{
    $dados = json_decode($response, true);

    if ($dados === null){
        $erro = "A API retornou um JSON inválido.";
    } elseif ($codigoHttp >= 400){
        $erro = "Erro da API: ".($dados["message"] ?? "Erro desconhecido.");
    }
}

$sucesso = ($erro === "");
```

| Situação | Como é detectada | Exemplo |
|---|---|---|
| Sem conexão, timeout ou falha de SSL | `curl_exec` retorna `false` | internet caiu |
| JSON inválido | `json_decode` retorna `null` | resposta corrompida |
| Erro da API | código HTTP maior ou igual a 400 | `?id=9999` (404) |
| Entrada inválida | `ctype_digit` falha | `?id=abc` |

Se nenhum erro foi registrado, `$sucesso` é verdadeiro. Uma busca sem resultados **não é erro**: a API responde normalmente (HTTP 200) e a página mostra "Nenhum produto encontrado".

## Por que `CURLOPT_SSL_VERIFYPEER` está como `false`?

```php
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
```

Por padrão, o cURL verifica se o certificado SSL do site (`https://`) é confiável, comparando com uma lista de certificados de autoridades (um arquivo `cacert.pem`). Em várias instalações do PHP no Windows (como o XAMPP), essa lista não está configurada, e a requisição falha com o erro *"SSL certificate problem: unable to get local issuer certificate"*.

Neste projeto a verificação foi desligada porque os computadores do laboratório da faculdade são resetados a cada aula, e baixar e configurar o `cacert.pem` toda vez seria inviável. Assim, o código funciona em qualquer máquina sem configuração extra.

**Atenção:** desligar essa verificação deixa a conexão vulnerável a interceptação (ataque *man-in-the-middle*), pois o PHP aceita qualquer certificado. Isso é aceitável em um exercício com uma API pública que não envia dados sensíveis, mas **não deve ser usado em projetos reais**.

Em uma máquina própria, o jeito correto é:

1. Baixar o arquivo `cacert.pem` em https://curl.se/docs/caextract.html.
2. Salvar em uma pasta, por exemplo `C:\xampp\php\extras\ssl\cacert.pem`.
3. No `php.ini`, configurar `curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"`.
4. Reiniciar o Apache e remover a linha do `VERIFYPEER`.

## Como rodar na sua máquina

**Requisito:** PHP com a extensão `curl` habilitada (no `php.ini`, a linha `extension=curl` sem `;` na frente). No XAMPP ela já costuma vir ativa.

### Opção 1: XAMPP

1. Instale o XAMPP (de preferência em `C:\xampp`).
2. Copie a pasta do projeto para `C:\xampp\htdocs\`.
3. Abra o painel do XAMPP e inicie o **Apache**.
4. Acesse no navegador: `http://localhost/prj_php_api/`

### Opção 2: servidor embutido do PHP (sem XAMPP)

1. Instale o PHP e confirme com `php -v` no terminal.
2. Entre na pasta do projeto e rode:

```bash
php -S localhost:8000
```

3. Acesse no navegador: `http://localhost:8000`

## Como testar

| Teste | Como fazer | Resultado esperado |
|---|---|---|
| Listagem | Abrir a página inicial | 12 produtos e faixa verde com HTTP 200 |
| Busca | Digitar `phone` e consultar | Produtos que combinam com o termo |
| Busca sem resultado | Digitar `zzzz` | "Nenhum produto encontrado" (sucesso) |
| Detalhe | Clicar em "Ver detalhes" ou digitar `5` no ID | Descrição, marca, avaliação e estoque |
| Erro da API | Digitar `9999` no ID | Faixa vermelha com HTTP 404 |
| ID inválido | Digitar `abc` no ID | "O ID precisa ser um número inteiro." |

## Tecnologias

PHP, cURL, HTML e CSS.
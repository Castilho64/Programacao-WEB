<?php

    $id = trim($_GET["id"] ?? ""); //trim serve para eliminar espaços em branco
    $busca = trim($_GET["q"] ?? ""); //guarda o texto que a pessoa digitar no campo "Buscar por termo"

    $url = "";
    $erro = "";
    $dados = null;
    $codigoHttp = 0;

    //Define qual endpoint será consultado
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
    

    //Faz a requisição GET com cURL
    if ($url !== ""){
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($curl);
        $codigoHttp = curl_getinfo($curl, CURLINFO_HTTP_CODE);

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
    }

    $sucesso = ($erro === "");

    //(?id=) retorna um produto. Lista e Busca retornam ["products"]
    $produtos = [];
    if ($sucesso){
        if ($id !== ""){
            $produtos = [$dados];
        } else{
            $produtos = $dados["products"];
        }
    }

?>
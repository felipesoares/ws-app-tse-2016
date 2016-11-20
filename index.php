<?php
  // importa o arquivo de configuração e as libs necessárias por autoload
  require 'config.inc.php';
  require 'vendor/autoload.php';

  // instancia a api
  $app = new \Slim\Slim();

  // método responsável por retornar os dados de autenticação dos usuários
  $app->get('/login/:cpf/:senha', function ($cpf, $senha) {

    $query = "SELECT * FROM `login` WHERE cpf = ? AND senha = md5(?)";
    global $DB;

    $rs = $DB->Execute($query, array($cpf, $senha));

    $sucesso = false;
    if( $rs->RecordCount() )
      $sucesso = true;
    
    $data = array(
      "success" => $sucesso
    );

    header('Content-Type: application/json');
    echo json_encode($data); exit;

  });

  // método responsável por retornar todas as cidades dos candidatos
  $app->get('/candidatos/cidades', function () {

    $query = "SELECT SIGLA_UE, DESCRICAO_UE FROM candidatos GROUP BY SIGLA_UE ORDER BY DESCRICAO_UE ASC";
    output($query);

  });

  // método responsável por retornar todos os dados de graus de escolaridade dos candidatos
  $app->get('/candidatos/graus-escolaridade', function () {

    $query = "SELECT COD_GRAU_INSTRUCAO, DESCRICAO_GRAU_INSTRUCAO FROM candidatos GROUP BY COD_GRAU_INSTRUCAO ORDER BY COD_GRAU_INSTRUCAO ASC";
    output($query);

  });

  // método responsável por retornar todos os dados de sexos dos candidatos
  $app->get('/candidatos/sexos', function () {

    $query = "SELECT CODIGO_SEXO, DESCRICAO_SEXO FROM candidatos GROUP BY DESCRICAO_SEXO ORDER BY CODIGO_SEXO ASC";
    output($query);

  });

  // método responsável por retornar todos os partidos eleitorais dos candidatos
  $app->get('/candidatos/partidos', function () {

    $query = "SELECT NUMERO_PARTIDO, SIGLA_PARTIDO FROM candidatos GROUP BY NUMERO_PARTIDO ORDER BY SIGLA_PARTIDO ASC";
    output($query);

  });

  // método responsável por retornar os dados dos candidatos
  $app->get('/candidatos/:cidade(/:cargo(/:grausEscolaridade)(/:sexos)(/:partidos))', function ($cidade, $cargo = null, $grausEscolaridade = null, $sexos = null, $partidos = null) {

    $where = "SIGLA_UE = {$cidade} AND NUM_TURNO = 1";
    
    if ($cargo) {
        $where .= " AND CODIGO_CARGO = {$cargo}";
    }
    
    if ($grausEscolaridade) {
        $where .= " AND COD_GRAU_INSTRUCAO IN({$grausEscolaridade})";
    }
    if ($sexos) {
        $where .= " AND CODIGO_SEXO IN({$sexos})";
    }
    if ($partidos) {
        $where .= " AND NUMERO_PARTIDO IN({$partidos})";
    }

    //echo "<pre>"; print_r($where); exit;
    $query = "SELECT CODIGO_CARGO,NOME_CANDIDATO,NOME_URNA_CANDIDATO,SIGLA_PARTIDO,DESCRICAO_GRAU_INSTRUCAO,DESCRICAO_COR_RACA,DESCRICAO_SEXO,DESCRICAO_ESTADO_CIVIL,IDADE_DATA_ELEICAO FROM candidatos WHERE {$where} ORDER BY NOME_CANDIDATO ASC";
    output($query);

  });

  // método responsável por retornar as zonas eleitorais de um determinado estado
  $app->get('/eleitorado/zonas-eleitorais/:uf', function ($uf) {

    $query = "SELECT DISTINCT(NR_ZONA) FROM eleitorado WHERE UF = '{$uf}' ORDER BY NR_ZONA ASC";
    output($query);

  });

  // método responsável por retornar todas as faixas etárias do eleitorado
  $app->get('/eleitorado/faixas-etarias', function () {

    $query = "SELECT FAIXA_ETARIA_ID, FAIXA_ETARIA FROM eleitorado GROUP BY FAIXA_ETARIA_ID ORDER BY FAIXA_ETARIA_ID ASC";
    output($query);

  });

  // método responsável por retornar todos os graus de escolaridade do eleitorado
  $app->get('/eleitorado/graus-escolaridade', function () {

    $query = "SELECT GRAU_DE_ESCOLARIDADE_ID, GRAU_DE_ESCOLARIDADE FROM eleitorado GROUP BY GRAU_DE_ESCOLARIDADE_ID ORDER BY GRAU_DE_ESCOLARIDADE_ID ASC";
    output($query);

  });

  // método responsável por retornar o total de eleitores de uma determinada zona eleitoral de um estado
  $app->get('/eleitorado/total/:uf/:zonaEleitoral', function ($uf, $zonaEleitoral) {

    $where = "UF = '{$uf}' AND NR_ZONA = {$zonaEleitoral}";

    $query = "SELECT SEXO, SUM(QTD_ELEITORES_NO_PERFIL) AS QUANTIDADE FROM eleitorado WHERE {$where} GROUP BY SEXO";
    output($query);

  });

  // método responsável por retornar os dados consolidados por sexo dos eleitores de uma determinada zona eleitoral de um estado
  $app->get('/eleitorado/:uf/:zonaEleitoral(/:faixasEtarias(/:grausEscolaridade))', function ($uf, $zonaEleitoral, $faixasEtarias = null, $grausEscolaridade = null) {

    $where = "UF = '{$uf}' AND NR_ZONA = {$zonaEleitoral}";
    
    if ($faixasEtarias) {
        $where .= " AND FAIXA_ETARIA_ID IN({$faixasEtarias})";
    }
    if ($grausEscolaridade) {
        $where .= " AND GRAU_DE_ESCOLARIDADE_ID IN({$grausEscolaridade})";
    }

    //echo "<pre>"; print_r($where); exit;
    $query = "SELECT SEXO, SUM(QTD_ELEITORES_NO_PERFIL) AS QUANTIDADE FROM eleitorado WHERE {$where} GROUP BY SEXO";
    output($query);

  });

  // inicia o webservice
  $app->run();

  // função responsável pela saída dos dados em formato JSON
  function output($query){

    global $DB;

    $DB->SetFetchMode(ADODB_FETCH_ASSOC);
    $rs = $DB->Execute($query);
    
    $data = array();

    if( $rs ){
      foreach ($rs as $row) {
          $data[] = $row;
        }
    }
    
    //print_r($data);
    header('Content-Type: application/json');
    echo json_encode($data); exit;
    
  }
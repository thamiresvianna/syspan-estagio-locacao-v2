<?php
    declare(strict_types=1);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function definirMensagem(string $texto, string $tipo = 'sucesso'): void {
        $_SESSION['mensagem'] = ['texto' => $texto, 'tipo' => $tipo];
    }

    function exibirMensagem(): void {
        if(empty($_SESSION['mensagem'])) {
            return;
        }

        $mensagem = $_SESSION['mensagem'];
        unset($_SESSION['mensagem']);

        $tipo = $mensagem['tipo'] === 'erro' ? 'erro' : 'sucesso';
        $papel = $tipo === 'erro' ? 'alert' : 'status';

        echo "<div class='mensagem mensagem-$tipo' role='$papel'>" . e($mensagem['texto']) . "</div>";
    }

    function redirecionarErro(string $texto, string $destino = 'listar.php'): never {
        definirMensagem($texto, 'erro');
        header("Location: $destino");
        exit;
    }

    function gerarTokenCsrf(): string {
        if(empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    function campoTokenCsrf(): string {
        $token = gerarTokenCsrf();

        return "<input type='hidden' name='csrf_token' value='" . e($token) . "'>";
    }

    function validarTokenCsrf(?string $token): bool {
        if (empty($_SESSION['csrf_token']) || $token === '') {
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], (string)$token);
    }

    function exigirCsrf(): void {
        $token = $_POST['csrf_token'] ?? '';

        if(!is_string($token) || !validarTokenCsrf($token)) {
            $destino = basename($_SERVER['PHP_SELF']);

            if(!empty($_SERVER['QUERY_STRING'])) {
                $destino .= '?' . $_SERVER['QUERY_STRING'];
            }

            redirecionarErro("Formulário inválido ou expirado. Recarregue a página e tente novamente.", $destino);
        }
    }

    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    function obterId(): int {
        $id = (int) ($_GET['id'] ?? 0);

        if($id <= 0){
            redirecionarErro("ID inválido.");
        }

        return $id;
    }

    function calcularStatusContrato(string $data_inicio, string $data_fim, ?string $data_hoje = null): string {
        $hoje = new DateTime($data_hoje ?? date('Y-m-d'));
        $inicio = new DateTime($data_inicio);
        $fim = new DateTime($data_fim);

        if ($hoje < $inicio){
            return 'AGENDADO';
        }
        if ($hoje > $fim){
            return 'ENCERRADO';
        }
        return 'ATIVO';
    }

    function calcularTotalDias(string $inicio, string $fim): int {
        $data_inicio = new DateTime($inicio);
        $data_fim = new DateTime($fim);

        return $data_inicio->diff($data_fim)->days + 1;
    }

    function mostrarErros(array $erros): void {
        foreach ($erros as $erro){
            echo "<div class='mensagem mensagem-erro' role='alert'>" . e($erro) . "</div>";
        }
    }

    function mostrarValor(mixed $valor): string {
        return !empty($valor) ? e($valor) : '-';
    }

    function numeroContrato(int $id): string {
        return str_pad((string)$id, 4, '0', STR_PAD_LEFT);
    }

    function limparNumeros(string $valor): string {
        return preg_replace('/\D/', '', $valor) ?? '';
    }

    function normalizarDecimal(string $valor): float {
        $valor = trim($valor);

        if(str_contains($valor, ',')){
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return (float) $valor;
    }

    function formatarMoeda(float $valor): string {
        return number_format($valor, 2, ',', '.');
    }

    function formatarInputDecimal(float $valor): string {
        return number_format($valor, 2, '.', '');
    }

    function formatarCpfCnpj(string $cpf_cnpj): string {
        $cpf_cnpj = limparNumeros($cpf_cnpj);

        if(strlen($cpf_cnpj) === 11){
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf_cnpj) ?? $cpf_cnpj;
        }
        if(strlen($cpf_cnpj) === 14){
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cpf_cnpj) ?? $cpf_cnpj;
        }

        return $cpf_cnpj;
    }

    function formatarTelefone(string $telefone): string {
        $telefone = limparNumeros($telefone);

        if(strlen($telefone) === 10){
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone) ?? $telefone;
        }
        if(strlen($telefone) === 11){
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone) ?? $telefone;
        }

        return $telefone;
    }

    function formatarCep(?string $cep): string {
        if(empty($cep)){
            return '-';
        }

        $cep = limparNumeros($cep);

        if(strlen($cep) === 8){
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $cep) ?? $cep;
        }

        return $cep;
    }

    function formatarData(?string $data): string {
        if(empty($data)){
            return '-';
        }

        $timestamp = strtotime($data);

        if($timestamp === false){
            return '-';
        }

        return date('d/m/Y - H:i',$timestamp);
    }

    function formatarDataSimples(?string $data): string {
        if(empty($data)){
            return '-';
        }

        $timestamp = strtotime($data);

        if($timestamp === false){
            return '-';
        }

        return date('d/m/Y',$timestamp);
    }

    function validarCliente(string $tipo_pessoa, string $nome, string $cpf_cnpj, string $email, 
                            string $telefone, string $cep, string $estado): array {                 
        $erros = [];

        $tipo_pessoa = trim($tipo_pessoa);
        $nome = trim($nome);
        $cpf_cnpj = limparNumeros($cpf_cnpj);
        $email = trim($email);
        $telefone = limparNumeros($telefone);
        $cep = limparNumeros($cep);
        $estado = trim($estado);

        if(!in_array($tipo_pessoa, ['F','J'])){
            $erros[] = "Tipo de pessoa inválido.";
        }
        if(strlen($nome) < 3 || strlen($nome) > 120){
            $erros[] = "Nome deve conter entre 3 e 120 caracteres.";
        }
        if(empty($cpf_cnpj)){
            $erros[] = "CPF/CNPJ é obrigatório.";
        } else {
            if($tipo_pessoa === 'F'){
                if(!validarCPF($cpf_cnpj)){
                    $erros[] = "CPF inválido.";
                }
            }
            if($tipo_pessoa === 'J'){
                if(!validarCNPJ($cpf_cnpj)){
                    $erros[] = "CNPJ inválido.";
                }
            }
        }
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            $erros[] = "E-mail inválido.";
        }
        if(strlen($telefone) < 10 || strlen($telefone) > 11){
            $erros[] = "Telefone inválido.";
        }
        if(!empty($cep) && !preg_match('/^\d{8}$/', $cep)){
            $erros[] = "CEP inválido.";
        }
        if(!empty($estado) && !preg_match('/^[A-Z]{2}$/', strtoupper($estado))){
            $erros[] = "Estado inválido.";
        }

        return $erros;
    }

    function validarCPF(string $cpf): bool {
        $cpf = limparNumeros($cpf);

        if(strlen($cpf) != 11){
            return false;
        }
        if(preg_match('/^(\d)\1{10}$/', $cpf)){
            return false;
        }

        $soma_primeiro_digito = 0;
        for($i = 0; $i < 9; $i++){
            $soma_primeiro_digito += $cpf[$i] * (10 - $i);
        }
        $resto = ($soma_primeiro_digito * 10) % 11;
        if($resto == 10){
            $resto = 0;
        }
        if($resto != $cpf[9]){
            return false;
        }

        $soma_segundo_digito = 0;
        for($i = 0; $i < 10; $i++){
            $soma_segundo_digito += $cpf[$i] * (11 - $i);
        }
        $resto = ($soma_segundo_digito * 10) % 11;
        if($resto == 10){
            $resto = 0;
        }
        if($resto != $cpf[10]){
            return false;
        }

        return true;
    }

    function validarCNPJ(string $cnpj): bool {
        $cnpj = limparNumeros($cnpj);

        if(strlen($cnpj) != 14){
            return false;
        }
        if(preg_match('/^(\d)\1{13}$/', $cnpj)){
            return false;
        }

        $peso1 = [5,4,3,2,9,8,7,6,5,4,3,2];
        $soma_peso1 = 0;
        for($i = 0; $i < 12; $i++){
            $soma_peso1 += $cnpj[$i] * $peso1[$i];
        }
        $resto = $soma_peso1 % 11;
        $digito1 = ($resto < 2) ? 0 : 11 - $resto;
        if($digito1 != $cnpj[12]){
            return false;
        }

        $peso2 = [6,5,4,3,2,9,8,7,6,5,4,3,2];
        $soma_peso2 = 0;
        for($i = 0; $i < 13; $i++){
            $soma_peso2 += $cnpj[$i] * $peso2[$i];
        }
        $resto = $soma_peso2 % 11;
        $digito2 = ($resto < 2) ? 0 : 11 - $resto;
        if($digito2 != $cnpj[13]){
            return false;
        }

        return true;
    }

    function validarEquipamento(string $codigo, string $descricao, string $categoria, 
                                string $marca, string $modelo, string $numero_serie): array {
        $erros = [];

        $codigo = trim($codigo);
        $descricao = trim($descricao);
        $categoria = trim($categoria);
        $marca = trim($marca);
        $modelo = trim($modelo);
        $numero_serie = trim($numero_serie);

        if(empty($codigo)){
            $erros[] = "Código é obrigatório.";
        }
        if(strlen($codigo) < 2 || strlen($codigo) > 30){
            $erros[] = "Código deve conter entre 2 e 30 caracteres.";
        }
        if(strlen($descricao) < 3 || strlen($descricao) > 120){
            $erros[] = "Descrição deve conter entre 3 e 120 caracteres.";
        }
        if(!empty($categoria) && (strlen($categoria) < 3 || strlen($categoria) > 80)){
            $erros[] = "Categoria deve conter entre 3 e 80 caracteres.";
        }
        if(!empty($marca) && (strlen($marca) < 3 || strlen($marca) > 80)){
            $erros[] = "Marca deve conter entre 3 e 80 caracteres.";
        }
        if(!empty($modelo) && (strlen($modelo) < 3 || strlen($modelo) > 80)){
            $erros[] = "Modelo deve conter entre 3 e 80 caracteres.";
        }
        if(!empty($numero_serie) && (strlen($numero_serie) < 3 || strlen($numero_serie) > 80)){
            $erros[] = "Número de série deve conter entre 3 e 80 caracteres.";
        }

        return $erros;
    }

    function validarPreco(string $nome, string $descricao): array {
        $erros = [];

        $nome = trim($nome);
        $descricao = trim($descricao);

        if(strlen($nome) < 3 || strlen($nome) > 120){
            $erros[] = "Nome deve conter entre 3 e 120 caracteres.";
        }
        if(!empty($descricao) && (strlen($descricao) < 3 || strlen($descricao) > 255)){
            $erros[] = "Descrição deve conter entre 3 e 255 caracteres.";
        }

        return $erros;
    }

    function validarPrecoItem(int $id_equipamento, float $valor_diaria): array {
        $erros = [];

        if($id_equipamento <= 0){
            $erros[] = "Equipamento inválido.";
        }
        if($valor_diaria <= 0){
            $erros[] = "Valor da diária deve ser maior que zero.";
        }

        return $erros;
    }

    function validarContrato(string $data_inicio, string $data_fim): array {
        $erros = [];

        $data_inicio = DateTime::createFromFormat('Y-m-d', trim($data_inicio));
        $data_fim = DateTime::createFromFormat('Y-m-d', trim($data_fim));

        if(!$data_inicio || !$data_fim){
            $erros[] = "As datas de início e fim são obrigatórias.";
            return $erros;
        }
        if($data_inicio > $data_fim){
            $erros[] = "A data de início não pode ser maior que a data de fim.";
        }

        return $erros;
    }

    function validarContratoItem(int $id_equipamento, int $qtd): array {
        $erros = [];

        if($id_equipamento <= 0){
            $erros[] = "Equipamento inválido.";
        }
        if($qtd <= 0){
            $erros[] = "Quantidade deve ser maior que zero.";
        }

        return $erros;
    }
?>
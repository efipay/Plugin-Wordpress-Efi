# Testes Unitários 

## Requisitos
- php >= 5;
- ![composer](https://getcomposer.org/)

## Comfiguração
- Primeiro copie a pasta 'tests' para a raiz do wordpress, instalado no servidor.
- Insira o certificado na pasta 'certs'.
- Insira suas credenciais no arquivo 'tests/gerencianet/pix/config.json':

```json
    "client_id_prod": "your_client_id_prod",
    "client_secret_prod": "your_client_secret_prod",
    "client_id_dev": "your_client_id_dev",
    "client_secret_dev": "your_client_secret_dev",
    "payee_code": "your_payee_code",
    "pix_key": "your_pix_key",
    "pix_cert" : "../../certs/your_cert_name.pem",
    "sandbox": false
```

## Rodando os testes
- Rode o seguinte comando na pasta de testes para instalar as dependências:
```shell
composer install
```
- Para iniciar os testes, rode:
```shell
vendor/bin/phpunit
```
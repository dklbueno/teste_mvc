# teste_mvc

Esta é uma aplicação modelo de MVC usando um Framework autoral.
Este teste utiliza como tecnologia: PHP, Mysql e AngularJs.
Nele foi criado um CRUD simples de usuário.
Para que funcione corretamente é necessário a criação de um banco Mysql com nome 'teste', e uma tabela chamada usuario:
CREATE TABLE usuario (
user_id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(30) NOT NULL,
email VARCHAR(50)
)
Após isso, deve-se alterar o arquivo 'system/config.php' nas linhas:
define( 'DB_HOST','localhost' );
define( 'DB_USER','root' );
define( 'DB_DBNAME','test' );
define( 'DB_PASS','1234' );

Não se esqueça de alterar também a constant PATH do config:
$path = 'http://localhost/teste_mvc/';

Obrigado!

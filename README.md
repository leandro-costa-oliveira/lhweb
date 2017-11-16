# Sobre
Um Framework Web, simples, bem enchuto, desenvolvido para meus próprios projetos, 
que venho disponibilizar para quem achar interessente utilizá-lo. Tenho aqui reunir
o máximo de boas práticas de programação que conheço e que acho conveniente. Qualquer
sugestão só entrar em contato. Muitos podem argumentar que é reinventar a roda,
mas minha grande motivação para criação meu próprio framework é a de ter uma ferramenta
personalizada para minhas necessidades, e bem enchuta.

---
# Requerimentos
Todo o desenvolvimento do framework está sendo feito com *php 5.5.26* em uma instalação
do Fedora 20.  E utilizando a camada de PDO para o banco de dados. Todo o resto é php 
puro.

---
# Instalação
A instalação pode ser feita atravéz do git ou baixando o arquivo zip no GitHUB:

`git clone https://github.com/lokidarkeden/lhweb`

ou

`wget https://github.com/lokidarkeden/lhweb/archive/master.zip && unzip master.zip` 

ou ainda através do composer, adicionando o requerimento no seu projeto.

---
# Utilização
Temos um exemplo de uso na pasta *web/index.php* do próprio projeto. Para ter
acesso as classes basta incluir o autoloader, que fica em *inc/autoloader.php*.


Temos 3 pacotes principais:

*lhweb\actions* composto hoje de uma única classe, a WebAction.
Cujo objetivo será tratar a request vinda dos usuários, validar e formatar os dados, e
deverá ser extendida por suas classes que cuidarão desta tarefa.

*lhweb\controller* é onde fica a classe para para suas classes de négocio, vocẽ deverá 
criar seus controladores extendendo  a class AbstractController, e neles desenvolver
a lógica de negócio da sua aplicação.

*lhweb\database* é onde temos as classes de conexão com o banco de dados. a *LHDB*
extende a classe PDO adicionando algumas funcionalidades, como por exemplo, manter
uma lista de conexões criadas para serem utilizadas pelas entidades. Temos a classe
GenericQuery que é basicamente uma fabrica de sql, com ele podemos contruir consultas 
sql de forma orientada a objetos, veja exemplos abaixo.

---
O primeiro passo é a criação de uma classe entidade, que irá representar uma tabela
do seu banco de dados, ela deve extender use lhweb\database\LHWebEntity;, vamos a um exemplo rápido:

```
use lhweb\database\LHWebEntity;

class MeuUsuario extends LHWebEntity {
    public $id;
    public $nome;
    public $login;
    public $senha;
}
```

Pronto, essa classe será mapeada pelo controlador automaticamente da seguinte forma:
    - Tabela nome meuusuario
    - campos: id, nome, login, senha.
    - chave primária: id, por padrão a chave primaria é chamada id, e o tipo int.

Para criar um controlador padrão, capaz de operações CRUD, basta fazer o seguinte:

```
use lhweb\controller\LHWebController;

$ctl = new LHWebController(MeuUsuario::class);

$primeiro = $ctl->primeiro(); // Obtem o primeiro registro ordenado pela Chave Primária.
$ultimo = $ctl->ultimo(); // Obtem o último registro ordenado pela Chave Primária.
$porpkk = $ctl->getByPK(5); // Obtem o registro pela chave primária passada como parâmetro.
$proximo = $ctl->proximo($porpkk->id); // Obtem o registro posterior da id passada.
$proximo = $ctl->anterior($porpkk->id); // Obtem o registro anterior da id passada.

// Efetuando um update em um registro
$usuario = $ctl->getByPK(5); // Obtem o Usuário
$usuario->nome = "Alterando o Nome";
$ctl->salvar($usuario);


// Removento um Registro
$ctl->apagar(5);

// Cadastrando um novo registro.
$u = new MeuUsuario();
$u->nome = "Usuario Novo";
$u->login = "Login do Usuario";
$u->senha = "123";
$u = $ctl->salvar($u); // Recebe o usuário salvo com a id gerada pelo banco de dados.
```

Em breve estarei postando exemplos mais avançados de relacionamentos entre tabelas.


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



# botGordice (para Slack)
Bot para mapear a gordice dos colegas da sua sala de trabalho no Slack.

É necessário possuir um servidor http com PHP e MySQL 5.6 mínimo. Ao menos até onde me conste.. 

Faça o upload dos arquivos para uma pasta no seu servidor, ajuste algums configurações no index.php e depois adicione um slash command ao Slack.

Abra o arquivo index.php em um editor qualquer de texto e ajuste as variáveis $appfolder, $imgfolder e $urltype como descrito no próprio arquivo.

$appfolder é a pasta onde se encontra o index.php no seu servidor
$imgfolder é a pasta para salvar a imagem gerada. Se quiser a mesma pasta, deixe apenas uma "/"
$urltype é o prefixo do URL do seu servidor, se é http ou https. 

Depois, adicione uma slash command ao Slack:

1. Faça o login no seu Slack team pela web ou pelo aplicativo, tanto faz
2. Clique no menu com o nome do time e escolha Customize Slack
3. Clique no menu (três linhas horizontais) e escolha Configure Apps
4. Clique em Custom Integrations
5. Clique em Slash Commands
6. Clique Add Configuration
7. Escolha um comando tipo /gordice e clique em Add Slack Command Integration
8. No URL coloque o local onde você colocou os arquivos
9. Escolha GET como método
10. Ignore o token
11. Escolha um nome ou simplesmente Gordice
12. Faça o upload de uma imagem ou escolha um ícone padrão
13. Escreva uma descrição do tipo “Controle das gordices da turma”
14. Escreva em Hit [add | del | update] ou help para ajuda.
15. Coloque a mesma descrição no Descriptive blablabla

Fechou. Agora é só ir no Slack e usar o comando recém criado /gordice em um canal, privado etc.

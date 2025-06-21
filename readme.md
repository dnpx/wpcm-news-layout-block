=== WPCM News Layout Block ===
Contributors: dopaixao
Tags: news, layout, block, shortcode, post, journal, newspaper, slideshow, carousel, excerpt
Requires at least: 5.5
Tested up to: 6.5
Stable tag: 2.3
Requires PHP: 7.4
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Crie um layout de notícias elegante e responsivo com imagens laterais em slideshow, usando um simples shortcode.

== Description ==

Transforme a exibição dos seus posts com o **WPCM News Layout Block**, um plugin poderoso e leve que cria um layout no estilo de jornal para destacar sua postagem mais recente. Ideal para homepages, portais de notícias ou qualquer área do site que precise de um visual impactante e profissional.

Com um único shortcode, você pode exibir o título, subtítulo, um resumo customizável e um carrossel de imagens que prende a atenção do leitor.

**Principais Funcionalidades:**

*   **Layout de Jornal Profissional:** Exibe o conteúdo de texto à esquerda e as imagens à direita, em um layout limpo e responsivo.
*   **Slideshow de Imagens Automático:** Se o seu post tiver mais de uma imagem (com altura mínima definida), o plugin cria automaticamente um slideshow com rotação a cada 2 segundos (customizável).
*   **Controle Total do Resumo:** Use a tag especial `[resumoXXX]` diretamente no seu post para definir o número exato de caracteres que devem aparecer no resumo, de forma invisível para o leitor.
*   **Alta Performance com Cache:** O resultado do bloco é armazenado em cache para garantir um carregamento ultrarrápido, com limpeza automática sempre que um post é atualizado.
*   **Totalmente Customizável:** Use os parâmetros do shortcode para filtrar por categoria, alterar a velocidade do slideshow, definir a altura mínima das imagens, mudar a cor de fundo e até remover a borda.
*   **Fácil de Usar:** Basta adicionar um simples shortcode a qualquer página, post ou widget.

Este plugin é a solução perfeita para quem busca um design de alta qualidade sem a complexidade de page builders pesados.

== Installation ==

A instalação é simples e rápida.

**1. Pelo Painel do WordPress (Recomendado):**

1.  No seu painel, vá para `Plugins > Adicionar Novo`.
2.  Procure por "WPCM News Layout Block".
3.  Clique em `Instalar Agora` e, em seguida, em `Ativar`.
4.  Pronto! Agora você pode usar o shortcode `[wpcm_news_layout]` em suas páginas.

**2. Manualmente (via FTP):**

1.  Faça o download do arquivo `.zip` do plugin.
2.  Descompacte o arquivo. Você terá uma pasta chamada `wpcm-news-layout-block`.
3.  Faça o upload desta pasta para o diretório `/wp-content/plugins/` do seu site.
4.  Vá para a página `Plugins` no seu painel do WordPress e ative o "WPCM News Layout Block".

== Frequently Asked Questions ==

= Como eu uso o plugin? =

Basta adicionar o shortcode `[wpcm_news_layout]` a qualquer página, post ou widget de texto onde você queira que o bloco de notícias apareça.

= Como posso customizar o shortcode? =

Você pode usar vários parâmetros. Por exemplo, para mostrar o post mais recente da categoria "esportes", sem borda e com um slideshow mais lento, você usaria:
`[wpcm_news_layout category="esportes" show_border="false" interval="4000"]`

= O slideshow de imagens não está aparecendo. Por quê? =

O slideshow só é ativado se o post em questão tiver **mais de uma imagem** que atenda ao critério de altura mínima (padrão de 400px). Verifique se o post tem múltiplas imagens anexadas ou na galeria e se elas são grandes o suficiente.

= Como funciona a tag `[resumoXXX]`? =

É uma instrução especial que você coloca **dentro do conteúdo do seu post**, não no shortcode. Por exemplo, para mostrar 250 caracteres, comece o texto do seu post com `[resumo250]`. Esta tag será invisível para seus leitores, mas instruirá o plugin a cortar o resumo nesse tamanho. Se a tag não for usada, o resumo padrão do WordPress será exibido.

= O plugin pareceu um pouco lento na primeira vez que carreguei a página. Isso é normal? =

Sim. Na primeira carga, o plugin gera o conteúdo, busca as imagens e salva tudo em um cache para otimizar a performance. As visitas seguintes à mesma página serão quase instantâneas, pois serão servidas diretamente do cache.

== Screenshots ==

1.  Exemplo do layout de notícias em ação, mostrando o conteúdo à esquerda e a imagem à direita.
2.  Detalhe do slideshow com múltiplas imagens e os pontos de navegação.
3.  Exemplo de uso da tag `[resumoXXX]` no editor de posts para controlar o tamanho do resumo.
4.  Exemplos de customização, um com cor de fundo diferente e outro sem a borda.
5.  O meta box "Subtítulo da Notícia" disponível na tela de edição de posts.

== Changelog ==

= 2.3 =
*   **Correção Crítica:** Corrigido o bug onde a tag `[resumoXXX]` podia aparecer no conteúdo público do post. A prioridade do filtro foi ajustada para garantir que a tag seja sempre removida antes da exibição.

= 2.2 =
*   **Novidade:** Adicionado o parâmetro `background_color` para permitir a customização da cor de fundo do bloco.
*   **Novidade:** Adicionado o parâmetro `show_border="false"` para permitir a remoção da borda do bloco.
*   **Melhoria:** Implementado um filtro global para remover a tag `[resumoXXX]` de todo o conteúdo público, impedindo que ela apareça em compartilhamentos sociais ou no próprio post.

= 2.1 =
*   **Novidade:** Implementada a funcionalidade de resumo dinâmico com a tag `[resumoXXX]` no editor de posts.
*   **Novidade:** Adicionado um botão "Continuar Lendo" com estilo discreto ao final do resumo.

= 2.0 =
*   **Novidade:** Refatoração completa para uma arquitetura orientada a objetos, mais limpa e segura.
*   **Novidade:** Implementado um sistema de cache com Transients para uma melhoria drástica de performance.
*   **Novidade:** Adicionado o arquivo JavaScript (`main.js`) para controlar o slideshow de imagens.
*   **Melhoria:** Otimizada a busca por imagens, substituindo `preg_match` por `get_attached_media` para maior velocidade.
*   **Melhoria:** CSS e JS agora são carregados condicionalmente apenas quando o shortcode está presente na página.

= 1.0 =
*   Lançamento inicial do plugin.

== Upgrade Notice ==

= 2.3 =
Esta versão corrige um bug importante onde a tag `[resumoXXX]` podia aparecer no conteúdo do post. Atualização altamente recomendada para garantir que a tag permaneça invisível para os leitores.

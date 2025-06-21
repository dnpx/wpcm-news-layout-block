<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação do Plugin: WPCM News Layout Block</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-gray: #ecf0f1;
            --medium-gray: #bdc3c7;
            --dark-gray: #7f8c8d;
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --code-bg: #e8eef2;
            --highlight-bg: #fffbe6;
            --highlight-border: #ffe58f;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.7;
            background-color: var(--bg-color);
            color: var(--primary-color);
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 20px auto;
            background-color: var(--card-bg);
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        h1, h2, h3 {
            font-weight: 700;
            line-height: 1.3;
            margin-top: 0;
            color: var(--primary-color);
        }

        h1 {
            font-size: 2.5rem;
            text-align: center;
            border-bottom: 2px solid var(--light-gray);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 1.8rem;
            margin-top: 40px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--medium-gray);
            color: var(--secondary-color);
        }

        h3 {
            font-size: 1.3rem;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        
        p {
            margin-bottom: 15px;
        }

        code {
            background-color: var(--code-bg);
            padding: 3px 6px;
            border-radius: 4px;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, Courier, monospace;
            font-size: 0.95em;
            color: #d6336c;
        }
        
        .code-block {
            background-color: var(--code-bg);
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #d1dce5;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            margin-bottom: 20px;
        }

        .code-block code {
            background-color: transparent;
            padding: 0;
            color: #333;
        }

        .parameter {
            margin-bottom: 25px;
            border-left: 4px solid var(--secondary-color);
            padding-left: 20px;
        }
        
        .highlight {
            background-color: var(--highlight-bg);
            border: 1px solid var(--highlight-border);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .highlight strong {
            color: #c47d00;
        }

        strong, b {
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            h1 { font-size: 2rem; }
            h2 { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Documentação: WPCM News Layout Block</h1>
        <p>Este guia detalha todas as funcionalidades e opções de shortcode disponíveis no plugin <strong>WPCM News Layout Block</strong>. Use este documento para customizar a exibição das suas notícias de forma rápida e eficiente.</p>

        <!-- Seção 1: Uso Básico -->
        <section id="basic-usage">
            <h2>Uso Básico</h2>
            <p>O shortcode mais simples exibe o post mais recente de qualquer categoria, usando todas as configurações padrão.</p>
            <div class="code-block">
                <code>[wpcm_news_layout]</code>
            </div>
            <p><strong>Configurações Padrão:</strong></p>
            <ul>
                <li>Exibe o post mais recente.</li>
                <li>Imagens rotacionam a cada 2 segundos.</li>
                <li>Altura mínima da imagem para inclusão é de 400px.</li>
                <li>O fundo é branco e a borda preta é visível.</li>
            </ul>
        </section>

        <!-- Seção 2: Parâmetros do Shortcode -->
        <section id="parameters">
            <h2>Parâmetros Disponíveis</h2>
            <p>Você pode adicionar parâmetros ao shortcode para customizar seu comportamento. Combine-os como precisar.</p>

            <div class="parameter">
                <h3><code>category</code></h3>
                <p>Filtra para exibir o post mais recente de uma categoria específica. Use o "slug" da categoria (o nome amigável para URL).</p>
                <p><strong>Exemplo:</strong></p>
                <div class="code-block"><code>[wpcm_news_layout category="tecnologia"]</code></div>
            </div>

            <div class="parameter">
                <h3><code>interval</code></h3>
                <p>Altera a velocidade de rotação do slideshow de imagens. O valor deve ser em milissegundos (1000 = 1 segundo).</p>
                <p><strong>Padrão:</strong> <code>2000</code></p>
                <p><strong>Exemplo (rotação a cada 5 segundos):</strong></p>
                <div class="code-block"><code>[wpcm_news_layout interval="5000"]</code></div>
            </div>

            <div class="parameter">
                <h3><code>min_height</code></h3>
                <p>Define a altura mínima em pixels que uma imagem deve ter para ser incluída no bloco.</p>
                <p><strong>Padrão:</strong> <code>400</code></p>
                <p><strong>Exemplo (apenas imagens com 500px de altura ou mais):</strong></p>
                <div class="code-block"><code>[wpcm_news_layout min_height="500"]</code></div>
            </div>

            <div class="parameter">
                <h3><code>background_color</code></h3>
                <p>Define uma cor de fundo para todo o bloco. Use um código de cor hexadecimal.</p>
                <p><strong>Padrão:</strong> <code>#ffffff</code> (branco)</p>
                <p><strong>Exemplo (fundo cinza claro):</strong></p>
                <div class="code-block"><code>[wpcm_news_layout background_color="#f5f5f5"]</code></div>
            </div>

            <div class="parameter">
                <h3><code>show_border</code></h3>
                <p>Controla a visibilidade da borda ao redor do bloco. Para remover a borda, use o valor <code>"false"</code>.</p>
                <p><strong>Padrão:</strong> <code>"true"</code></p>
                <p><strong>Exemplo (bloco sem borda):</strong></p>
                <div class="code-block"><code>[wpcm_news_layout show_border="false"]</code></div>
            </div>
        </section>

        <!-- Seção 3: Funcionalidade Especial -->
        <section id="special-feature">
            <h2>Funcionalidade Especial: Resumo Dinâmico</h2>
            <p>Para um controle preciso sobre o tamanho do resumo exibido, você pode usar uma tag especial diretamente no editor de posts do WordPress.</p>
            
            <div class="highlight">
                <p><strong>Importante:</strong> Esta tag é uma <strong>instrução para o sistema</strong> e deve ser colocada no <strong>corpo do texto do seu post</strong>, não no shortcode. Ela é <strong>totalmente invisível</strong> para os leitores do seu site e para as prévias de compartilhamento em redes sociais.</p>
            </div>

            <h3>Formato da Tag: <code>[resumoXXX]</code></h3>
            <p>Substitua <code>XXX</code> pelo número exato de caracteres que você deseja exibir no resumo.</p>
            
            <p><strong>Exemplo 1 (mostrar 300 caracteres):</strong></p>
            <p>No editor do seu post, comece o texto assim:</p>
            <div class="code-block"><code>[resumo300]Especialistas identificaram um vazamento de dados que pode ter atingido mais de 16 bilhões de senhas...</code></div>

            <p><strong>Exemplo 2 (mostrar 180 caracteres para uma chamada curta):</strong></p>
            <div class="code-block"><code>[resumo180]Um grande vazamento de dados afetou senhas da Apple, Meta e Google, segundo investigação da Cybernews...</code></div>
            <p>Se a tag não for encontrada no post, o plugin usará o resumo padrão do WordPress automaticamente.</p>
        </section>
        
        <!-- Seção 4: Exemplos Combinados -->
        <section id="combined-examples">
            <h2>Exemplos Combinados</h2>
            <p>Veja como combinar múltiplos parâmetros para um resultado totalmente customizado.</p>
            
            <p><strong>Exemplo:</strong> Exibir a notícia mais recente da categoria "viagens", sem borda, com fundo azul claro, e com um slideshow que troca de imagem a cada 4 segundos.</p>
            <div class="code-block">
                <code>[wpcm_news_layout category="viagens" show_border="false" background_color="#eaf4ff" interval="4000"]</code>
            </div>
        </section>

        <!-- Seção 5: Observações -->
        <section id="notes">
            <h2>Observações Finais</h2>
            <ul>
                <li><strong>Cache:</strong> Para garantir alta performance, o resultado do bloco é salvo em um cache (transient) por 1 hora.</li>
                <li><strong>Atualização Automática:</strong> O cache é limpo automaticamente sempre que você salva ou atualiza um post, garantindo que o conteúdo exibido esteja sempre atualizado.</li>
            </ul>
        </section>

    </div>

</body>
</html>

# NB Contabilidade

Site institucional estático em HTML, CSS e JavaScript.

## Arquivos
- index.html: página e estilos do site
- nb-logo-oficial.png e nb-logo.png: logos
- nicoly-bueno-costa.jpeg: foto da responsável técnica
- escritorio-nb-tratado.png: foto do escritório
- robots.txt, sitemap.xml e llms.txt: arquivos de descoberta

## Publicação
O site está publicado em https://nbcont.com.br, na hospedagem HostGator (Plano M, cPanel).
Para atualizar, envie os arquivos da raiz deste repositório para `public_html` pelo
Gerenciador de Arquivos do cPanel, incluindo o `.htaccess`.

- `.htaccess`: força HTTPS e redireciona `www` para `nbcont.com.br`; mantém o handler PHP 8.2 do cPanel.
- `enviar.php`: recebe o formulário de contato e envia para contato@nbcont.com.br
  (campo isca, tempo mínimo de preenchimento e limite de 5 envios por hora por IP).

## Contato configurado
- WhatsApp: (19) 99230-1230
- Formulário: contato@nbcont.com.br

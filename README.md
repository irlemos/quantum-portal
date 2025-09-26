
# 🚀 QuantumPortal - Portal Automatizado com IA e WordPress

Este projeto demonstra a criação de **QuantumPortal**, um portal de matérias totalmente automatizado, utilizando **IA, WordPress, integrações e automações**.  

---

## 🌐 Demonstração Online

Um portal desenvolvido utilizando este fluxo do **QuantumPortal** está ativo e disponível como exemplo prático:  
👉 [satuni.com.br](https://satuni.com.br)

> 🔎 Observação: Satuni é um **caso de uso real** do fluxo de automação descrito.  
> Ele integra-se ao Google Ads para exibição de anúncios e pode ser adaptado para qualquer tema ou nicho de interesse.

---

## ⚙️ Como Funciona

### 1. Entrada de Dados  
- Cada **subdomínio** possui uma planilha no **Google Drive**, listando os temas a serem abordados.  
- Os agentes de IA processam essa lista diariamente.  

### 2. Geração de Conteúdo  
- **Google Gemini via Make**: gera as matérias, faz SEO (títulos, descrições, tags, categorias).  
- **Runware**: gera imagens para ilustrar cada publicação.  

### 3. Publicação Automática  
- Cada **subdomínio** é uma instalação **WordPress independente**.  
- Os conteúdos são publicados automaticamente com todo o SEO otimizado.  

### 4. Portal Agregador  
- O **domínio principal** não tem conteúdo próprio.  
- Ele funciona como **agregador das matérias** dos subdomínios.  
- Plugin WordPress customizado conecta os subdomínios, exibe os assuntos e as últimas matérias publicadas.  

### 5. Monetização  
- O portal está integrado ao **Google Ads**, exibindo anúncios para geração de receita.  

---

## 🏗️ Arquitetura do Projeto

A estrutura é composta por:

- **Domínio principal**: funciona como um **agregador**.  
- **Subdomínios**: cada um representa um **assunto específico**, gerando matérias automaticamente via IA.  

### 📐 Wireframe da Arquitetura

```
[Google Drive Planilhas] ---> [Make + Gemini + Runware]
         |                             |
         v                             v
   [Subdomínio A - WP]   [Subdomínio B - WP] ... [Subdomínio N - WP]
         \                  |                      /
          \-----------------|---------------------/
                           v
                  [Domínio Principal - WP Agregador]
                           |
                           v
                     [Exibição + Google Ads]
```

---

## 🔌 Tecnologias Utilizadas

- **WordPress** → CMS para os portais (principal e subdomínios). (https://wordpress.org/)
- **Plugins WordPress customizados** → integração com IA via Make e agregação de subdomínios.  
- **Google Drive** → planilhas como base de dados de temas. (https://drive.google.com/)
- **Google Gemini (IA)** → geração de conteúdo otimizado. (https://aistudio.google.com/)
- **Runware** → geração automática de imagens. (https://runware.ai/)
- **Make (Integromat)** → automações e orquestração dos fluxos. (https://www.make.com/)
- **Google Ads** → monetização via exibição de anúncios. (https://adsense.google.com/)

---

## 👤 Sobre o Autor

Desenvolvido por [Rodrigo Lemos](https://linkedin.com/in/irlemos)  

💻 **Experiência ampla em desenvolvimento de software, integrações e soluções complexas**  
Com vasta experiência em múltiplas linguagens, plataformas e projetos escaláveis.

---

## 📜 Licença

Este projeto é demonstrativo e faz parte do meu portfólio profissional.  
O uso comercial desta arquitetura requer autorização prévia.

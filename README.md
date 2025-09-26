# QuantumPortal

🌐 Available in: [English](README.md) | [Português BR](README.pt-br.md)


# 🚀 QuantumPortal - Automated Portal with AI and WordPress

This project demonstrates the creation of **QuantumPortal**, a fully automated article portal using **AI, WordPress, integrations, and automations**.  

---

## 🌐 Live Demo

A portal developed using this **QuantumPortal** flow is live and available as a practical example:  
👉 [satuni.com.br](https://satuni.com.br)

> 🔎 Note: Satuni is a **real use case** of the described automation flow.  
> It integrates with Google Ads for displaying ads and can be adapted to any theme or niche.

---

## ⚙️ How It Works

### 1. Data Input  
- Each **subdomain** has a **Google Drive** spreadsheet listing the topics to be covered.  
- AI agents process this list daily.  

### 2. Content Generation  
- **Google Gemini via Make**: generates articles, performs SEO (titles, descriptions, tags, categories).  
- **Runware**: generates images to illustrate each publication.  

### 3. Automatic Publishing  
- Each **subdomain** is an independent **WordPress** installation.  
- Content is published automatically with SEO fully optimized.  

### 4. Aggregator Portal  
- The **main domain** has no original content.  
- It acts as an **aggregator of articles** from the subdomains.  
- A custom WordPress plugin connects the subdomains, displaying the topics and the latest published articles.  

### 5. Monetization  
- The portal is integrated with **Google Ads**, displaying ads to generate revenue.  

---

## 🏗️ Project Architecture

The structure consists of:

- **Main domain**: acts as an **aggregator**.  
- **Subdomains**: each one represents a **specific topic**, automatically generating articles via AI.  

### 📐 Architecture Wireframe

```
[Google Drive Sheets] ---> [Make + Gemini + Runware]
         |                             |
         v                             v
   [Subdomain A - WP]   [Subdomain B - WP] ... [Subdomain N - WP]
         \                  |                      /
          \-----------------|---------------------/
                           v
                  [Main Domain - WP Aggregator]
                           |
                           v
                     [Display + Google Ads]
```

---

## 🔌 Technologies Used

- **WordPress** → CMS for the portals (main and subdomains). (https://wordpress.org/)  
- **Custom WordPress Plugins** → integration with AI via Make and aggregation of subdomains.  
- **Google Drive** → spreadsheets as the base of topic data. (https://drive.google.com/)  
- **Google Gemini (AI)** → optimized content generation. (https://aistudio.google.com/)  
- **Runware** → automatic image generation. (https://runware.ai/)  
- **Make (Integromat)** → automations and flow orchestration. (https://www.make.com/)  
- **Google Ads** → monetization via ad display. (https://adsense.google.com/)  

---

## 👤 About the Author

Developed by [Rodrigo Lemos](https://linkedin.com/in/irlemos)  

💻 **Extensive experience in software development, integrations, and complex solutions**  
With vast expertise in multiple programming languages, platforms, and scalable projects.

---

## 📜 License

This project is demonstrative and part of my professional portfolio.  
Commercial use of this architecture requires prior authorization.

# Ticketing App — Twig + PHP + Local JSON Storage

Welcome to your lightweight ticket management system built with **Twig**, **PHP**, and **Tailwind CSS**. This project is a server-rendered alternative to your Vue-based app, designed for simplicity, speed, and full control, no external APIs, no databases, just clean code and local storage.

---

## 🚀 What This App Does

- Create, edit, and delete support tickets
- Session-based login/logout system
- Flash messages for user feedback
- Confirmation prompts before deleting
- Responsive UI with Tailwind CSS
- All data stored locally in `tickets.json`

---

## 🧠 How It Works

This app uses a **local JSON file** as its data store — no database or external API required.

### 📦 Ticket Storage

All tickets are saved in: /storage/tickets.json


Each ticket includes:

```json
{
  "id": 1690000000,
  "title": "Example Ticket",
  "status": "open",
  "description": "Optional description",
  "created_at": "2025-10-28 11:55:00"
}
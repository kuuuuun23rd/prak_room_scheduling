# TUM Room Scheduling System

A CPEE-driven web application that displays real-time room availability and schedules for TUM (Technical University of Munich). Users interact with the system through QR codes displayed on a large screen. By scanning QR codes, users can view detailed room information and navigate between rooms.

---

## System Overview

The system consists of two main display pages and a PHP backend. A CPEE workflow controls the process — showing the overview page, waiting for a QR scan, then showing the detail page for the selected room.

## Demo

_Add a screenshot or video of the system here_

---

### Prepare and Finalize

Each CPEE node has two data-handling sections:

- **Prepare** — runs *before* the node executes. It is used to set up endpoints or pass data into the node. For example, `endpoints.display += attributes.frames_id` registers which browser frame this node should display its page in.
- **Finalize** — runs *after* the node receives a result (e.g. a QR code scan). The result returned by `send.php` is captured into an access variable and then stored in a CPEE data field so it can be used by later nodes.

---

## Configuration

All configuration is done via **CPEE Data Objects** — nothing is hardcoded in the HTML files. This means you can fully customize the system without touching any code.

### Customizable Data Objects

| Name | Example Value | Description |
|------|--------------|-------------|
| `rooms` | `5602.EG.001,5620.01.101,5611.01.018` | Comma-separated list of room IDs to display on the overview page. Add or remove rooms here. |
| `chair` | `i17` | Chair name displayed in the header of both pages. Change this to match your chair. |
| `comments` | `Practical course rooms for i17` | Subtitle displayed under the chair name. Use this for course name or any description. |
| `send_url` | `https://lehre.bpm.in.tum.de/~ge59tah/qr/send.php` | URL of the PHP callback relay on your server. |

### How to Configure

1. Open your CPEE instance
2. Go to **Data Objects** tab
3. Set the values for `rooms`, `chair`, `comments`, and `send_url`
4. Start the workflow — the pages will automatically use your configured values

No code changes are needed. The HTML pages read all values from CPEE Page Parameters which are populated from the Data Objects.

### CPEE Workflow Structure

<img width="533" height="679" alt="Bildschirmfoto 2026-04-13 um 22 51 35" src="https://github.com/user-attachments/assets/f89fabcb-cb0f-42b7-b517-e7d95a96c8f7" />


---

### CPEE Node Descriptions

**a1 – Init Frame:** Initialises the browser frame. Prepare code: `endpoints.display += attributes.frames_id` and `data.timeout = Time.now.to_i`.

**a2 – Clear:** Clears the current display so the next page loads cleanly.

**a3 – Show Overview:** Opens `room_overview.html` and waits for the user to scan a room QR code. When scanned, `send.php` sends `selected_room` and `availability` back to CPEE. Finalize stores them in `data.selected_room` and `data.availability`.

**a9 – Set Timeout to Current Time:** Script: `data.timeout = Time.now.to_i`. Resets the inactivity timer after the overview page interaction.

**a4 – Show Details:** Opens `room_details.html` passing `data.selected_room` as the `room` URL parameter. When the user scans the go-back QR, `send.php` sends `go_back=true` and `availability` back to CPEE. Finalize stores them in `data.go_back` and `data.availability`.

**a10 – Set Timeout to Current Time:** Script: `data.timeout = Time.now.to_i`. Resets the inactivity timer after the detail page interaction.

**a7 – Wait 2 minutes:** Timeout node running in parallel with the main loop. If 120 seconds pass with no QR scan, the process finishes automatically.

---

## File Descriptions

### Display Pages

| File | URL Parameters | Purpose |
|------|---------------|---------|
| `room_overview.html` | `rooms`, `chair`, `course`, `send_url` | Main dashboard showing all configured rooms with availability status and today's schedule. Each room has a QR code. |
| `room_details.html` | `room`, `chair`, `course`, `send_url` | Detail page for a single room showing address, type, today's next event, and full weekly calendar grid. Includes a go-back QR code. |

### PHP Backend

| File | Purpose |
|------|---------|
| `qr/send.php` | CPEE callback relay. Receives `selected_room`, `availability`, or `go_back` via GET/POST and does a PUT request to the CPEE callback URL with the data as JSON. This is how QR code scans communicate back to the CPEE engine. |

---

## Data Objects

| Name | Default | Description |
|------|---------|-------------|
| `rooms` | `5602.EG.001,...` | Comma-separated list of room IDs to display |
| `selected_room` | `nil` | Currently selected room ID (set when QR is scanned) |
| `availability` | `unknown` | Room availability: `free` or `not_free` |
| `chair` | `i17` | Chair name shown in the header |
| `comments` | `Practical course rooms for i17` | Subtitle shown in the header |
| `send_url` | `https://...send.php` | URL of the PHP callback relay |
| `timeout` | `0` | Unix timestamp for timeout tracking |
| `go_back` | `false` | Set to `true` when user scans the go-back QR |

---

## Page Parameters

### a3 – Show Overview
| Parameter | Value |
|-----------|-------|
| `rooms` | `!data.rooms` |
| `chair` | `!data.chair` |
| `course` | `!data.comments` |
| `send_url` | `!data.send_url` |

### a4 – Show Details
| Parameter | Value |
|-----------|-------|
| `room` | `!data.selected_room` |
| `chair` | `!data.chair` |
| `course` | `!data.comments` |
| `send_url` | `!data.send_url` |

---

## External APIs Used

| API | Used By |
|-----|---------|
| [NavigaTUM Calendar API](https://nav.tum.de/api/calendar) | `room_overview.html`, `room_details.html` |
| [NavigaTUM Locations API](https://nav.tum.de/api/locations) | `room_details.html` |
| [QRCode.js](https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js) | Both display pages |
| [CPEE](https://cpee.org) | Workflow engine |

---

## How QR Code Interaction Works

1. **Room QR (on overview page):** Encodes a URL like `send.php?cb=<callback>&selected_room=<id>&availability=<free|not_free>`. When scanned, `send.php` PUTs this data to CPEE, which stores it in data objects and advances to Show Details.

2. **Go-back QR (on detail page):** Encodes a URL like `send.php?cb=<callback>&go_back=true&availability=<free|not_free>`. When scanned, CPEE receives `go_back=true` and loops back to Show Overview.

---

## Possible Improvements

- **Timeout handling** — show a countdown timer on screen so users know how long the detail page will stay visible
- **Room capacity** — NavigaTUM provides capacity data that could be shown on the detail page
- **Floor plan link** — add a link to the NavigaTUM map for each room
- **Multiple languages** — support German and English based on browser language

---

## About

Built as part of the Practical Course WS25 at i17 — Lehrstuhl für Wirtschaftsinformatik und Geschäftsprozessmanagement, TUM.

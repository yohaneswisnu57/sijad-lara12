#!/bin/bash
# ═══════════════════════════════════════════════════════════════════════════════
#  SIJAD — Docker Build & Deploy Script
#  Universitas Katolik Widya Mandala Surabaya
#
#  Usage:
#    chmod +x build.sh   ← hanya pertama kali
#    ./build.sh           ← jalankan menu
# ═══════════════════════════════════════════════════════════════════════════════

set -e

# ── Warna terminal ─────────────────────────────────────────────────────────────
RED='\033[0;31m';  GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m';  BOLD='\033[1m';  NC='\033[0m'

APP_NAME="sijad_app"
COMPOSE_FILE="docker-compose.yml"
ENV_FILE=".env.docker"

# ── Helper functions ───────────────────────────────────────────────────────────
info()    { echo -e "${CYAN}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; }
header()  { echo -e "\n${BOLD}${BLUE}══════════════════════════════════════════${NC}"; \
            echo -e "${BOLD}${BLUE}  $1${NC}"; \
            echo -e "${BOLD}${BLUE}══════════════════════════════════════════${NC}"; }

# ── Cek prasyarat ──────────────────────────────────────────────────────────────
check_requirements() {
    if ! command -v docker &>/dev/null; then
        error "Docker tidak ditemukan. Install Docker terlebih dahulu."
        exit 1
    fi
    if ! docker compose version &>/dev/null; then
        error "Docker Compose plugin tidak ditemukan."
        exit 1
    fi
    if [ ! -f "$COMPOSE_FILE" ]; then
        error "File $COMPOSE_FILE tidak ditemukan. Jalankan dari root project."
        exit 1
    fi
    if [ ! -f "$ENV_FILE" ]; then
        warn "File $ENV_FILE tidak ditemukan!"
        warn "Salin dan sesuaikan: cp .env.example $ENV_FILE"
        echo -e "Lanjutkan tanpa $ENV_FILE? [y/N] \c"
        read -r ans
        [[ "$ans" =~ ^[Yy]$ ]] || exit 1
    fi
}

# ══════════════════════════════════════════════════════════════════════════════
#  MENU AKSI
# ══════════════════════════════════════════════════════════════════════════════

# 1. Build & Start (deploy pertama kali / update)
action_build_start() {
    header "Build & Start Container"
    info "Build image dan jalankan container..."
    docker compose -f "$COMPOSE_FILE" up -d --build
    success "Container berhasil dijalankan."
    info "Cek status: docker compose ps"
    info "Cek log:    docker compose logs -f $APP_NAME"
}

# 2. Start tanpa rebuild (jika image sudah ada)
action_start() {
    header "Start Container (tanpa rebuild)"
    docker compose -f "$COMPOSE_FILE" up -d
    success "Container dijalankan."
}

# 3. Stop container
action_stop() {
    header "Stop Container"
    docker compose -f "$COMPOSE_FILE" stop
    success "Container dihentikan."
}

# 4. Restart container
action_restart() {
    header "Restart Container"
    docker compose -f "$COMPOSE_FILE" restart
    success "Container di-restart."
}

# 5. Stop & hapus container (tapi pertahankan volume/data)
action_down() {
    header "Stop & Hapus Container"
    warn "Ini akan MENGHAPUS container, tapi VOLUME DATA tetap aman."
    echo -e "Lanjutkan? [y/N] \c"
    read -r ans
    [[ "$ans" =~ ^[Yy]$ ]] || { info "Dibatalkan."; return; }
    docker compose -f "$COMPOSE_FILE" down
    success "Container dihapus. Volume data masih ada."
}

# 6. Hapus total (termasuk volume — ⚠️ DATA HILANG)
action_down_volumes() {
    header "⚠️  Hapus Container + Volume (DATA HILANG)"
    warn "INI AKAN MENGHAPUS semua data di volume (file SK, upload, logs)!"
    echo -e "${RED}Ketik 'HAPUS' untuk konfirmasi: ${NC}\c"
    read -r ans
    [ "$ans" = "HAPUS" ] || { info "Dibatalkan."; return; }
    docker compose -f "$COMPOSE_FILE" down -v
    success "Container dan volume dihapus."
}

# 7. Lihat log real-time
action_logs() {
    header "Log Container — $APP_NAME"
    info "Tekan Ctrl+C untuk keluar dari log."
    docker compose -f "$COMPOSE_FILE" logs -f "$APP_NAME"
}

# 8. Masuk ke shell container (bash)
action_shell() {
    header "Shell Container — $APP_NAME"
    info "Masuk ke shell container. Ketik 'exit' untuk keluar."
    docker compose -f "$COMPOSE_FILE" exec "$APP_NAME" bash || \
    docker compose -f "$COMPOSE_FILE" exec "$APP_NAME" sh
}

# 9. Jalankan artisan di dalam container
action_artisan() {
    header "Laravel Artisan"
    echo -e "Perintah artisan (tanpa 'php artisan'): \c"
    read -r cmd
    [ -z "$cmd" ] && { warn "Perintah kosong."; return; }
    docker compose -f "$COMPOSE_FILE" exec "$APP_NAME" php artisan $cmd
}

# 10. Status container
action_status() {
    header "Status Container"
    docker compose -f "$COMPOSE_FILE" ps
    echo ""
    info "Penggunaan resource:"
    docker stats --no-stream "$APP_NAME" 2>/dev/null || warn "Container tidak berjalan."
}

# 11. Clear cache Laravel di dalam container
action_clear_cache() {
    header "Clear Cache Laravel"
    info "Membersihkan semua cache aplikasi..."
    docker compose -f "$COMPOSE_FILE" exec "$APP_NAME" php artisan optimize:clear
    success "Cache dibersihkan."
}

# ══════════════════════════════════════════════════════════════════════════════
#  MAIN MENU
# ══════════════════════════════════════════════════════════════════════════════
show_menu() {
    clear
    echo -e "${BOLD}${BLUE}"
    echo "  ╔════════════════════════════════════════════╗"
    echo "  ║        SIJAD — Docker Manager              ║"
    echo "  ║   Universitas Katolik Widya Mandala        ║"
    echo "  ╚════════════════════════════════════════════╝"
    echo -e "${NC}"
    echo -e "  ${GREEN}1)${NC} 🚀  Build & Start    ${CYAN}(deploy / update)${NC}"
    echo -e "  ${GREEN}2)${NC} ▶️   Start            ${CYAN}(container sudah ada)${NC}"
    echo -e "  ${GREEN}3)${NC} ⏹️   Stop             ${CYAN}(hentikan container)${NC}"
    echo -e "  ${GREEN}4)${NC} 🔄  Restart          ${CYAN}(restart container)${NC}"
    echo -e "  ${GREEN}5)${NC} 🗑️   Down             ${CYAN}(hapus container, data aman)${NC}"
    echo -e "  ${RED}6)${NC} ☠️   Down + Volumes   ${CYAN}(hapus SEMUA termasuk data)${NC}"
    echo    "  ─────────────────────────────────────────────"
    echo -e "  ${GREEN}7)${NC} 📋  Lihat Log        ${CYAN}(real-time)${NC}"
    echo -e "  ${GREEN}8)${NC} 🖥️   Shell Container  ${CYAN}(bash/sh)${NC}"
    echo -e "  ${GREEN}9)${NC} ⚙️   Artisan Command  ${CYAN}(php artisan ...)${NC}"
    echo -e " ${GREEN}10)${NC} 📊  Status           ${CYAN}(ps + resource usage)${NC}"
    echo -e " ${GREEN}11)${NC} 🧹  Clear Cache      ${CYAN}(optimize:clear)${NC}"
    echo    "  ─────────────────────────────────────────────"
    echo -e "  ${YELLOW}0)${NC} ❌  Keluar"
    echo ""
    echo -e "  Pilihan [0-11]: \c"
}

# ── Entry point ────────────────────────────────────────────────────────────────
check_requirements

# Mode non-interaktif: ./build.sh <nomor>
if [ -n "$1" ]; then
    case "$1" in
        1) action_build_start ;;
        2) action_start ;;
        3) action_stop ;;
        4) action_restart ;;
        5) action_down ;;
        6) action_down_volumes ;;
        7) action_logs ;;
        8) action_shell ;;
        9) action_artisan ;;
        10) action_status ;;
        11) action_clear_cache ;;
        *) error "Pilihan tidak valid: $1"; exit 1 ;;
    esac
    exit 0
fi

# Mode interaktif (menu)
while true; do
    show_menu
    read -r choice
    case "$choice" in
        1)  action_build_start ;;
        2)  action_start ;;
        3)  action_stop ;;
        4)  action_restart ;;
        5)  action_down ;;
        6)  action_down_volumes ;;
        7)  action_logs ;;
        8)  action_shell ;;
        9)  action_artisan ;;
        10) action_status ;;
        11) action_clear_cache ;;
        0)  echo -e "\n${GREEN}Sampai jumpa!${NC}\n"; exit 0 ;;
        *)  warn "Pilihan tidak valid. Tekan Enter untuk lanjut."
            read -r ;;
    esac
    echo ""
    echo -e "${YELLOW}Tekan Enter untuk kembali ke menu...${NC}"
    read -r
done

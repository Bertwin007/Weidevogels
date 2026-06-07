#!/usr/bin/env bash
# Toon laatste Laravel-fout (niet alleen stacktrace)
LOG="${1:-$HOME/httpdocs/storage/logs/laravel.log}"
grep '\.ERROR:' "$LOG" | tail -3

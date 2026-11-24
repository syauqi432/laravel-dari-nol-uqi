<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portofolio Saham Ultimate Pro - Uqi</title>
    <!-- CDN Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: #1e293b;
            --text-color: #f8fafc;
            --border-color: #334155;
            --table-hover: #273548;
        }
        .light-mode {
            --bg-color: #f1f5f9;
            --card-bg: #ffffff;
            --text-color: #0f172a;
            --border-color: #cbd5e1;
            --table-hover: #f8fafc;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            transition: background 0.3s, color 0.3s;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header h2 { margin: 0; }
        .btn-group { display: flex; gap: 10px; align-items: center; }
        .btn-tambah { background-color: #10b981; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; }
        .btn-tambah:hover { background-color: #059669; }
        .btn-theme { background-color: #64748b; color: white; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .top-tools { display: flex; justify-content: space-between; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
        .search-form, .filter-form { display: flex; gap: 10px; }
        input, select { padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border-color); background: var(--card-bg); color: var(--text-color); }
        .btn-action { padding: 8px 16px; background: #38bdf8; border: none; border-radius: 6px; color: white; cursor: pointer; font-weight: bold; }
        .btn-reset { padding: 8px 12px; background: #64748b; border-radius: 6px; color: white; text-decoration: none; display: flex; align-items: center; }

        .summary-box, .chart-box {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #38bdf8;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border-color); font-size: 14px; }
        th { background-color: var(--border-color); font-weight: 600; }
        tr:hover { background-color: var(--table-hover); }
        .badge { background-color: #3b82f6; color: white; padding: 4

-- Table election --
CREATE TABLE IF NOT EXISTS election (
    path TEXT PRIMARY KEY,
    accountType TEXT,
    yearOrSerial INTEGER,
    electionYear INTEGER,
    electionName TEXT,
    electionArea TEXT,
    type TEXT,
    pdfFileName TEXT,
    csvFileName TEXT,
    zipFileName TEXT,
    isBackend INTEGER,
    downloadPdf TEXT,
    downloadCsv TEXT,
    downloadZip TEXT
);

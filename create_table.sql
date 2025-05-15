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

-- Table account --
CREATE TABLE IF NOT EXISTS account (
    path TEXT PRIMARY KEY,
    accountNumber TEXT,
    accountType TEXT,
    yearOrSerial INTEGER,
    version INTEGER,
    electionYear INTEGER,
    electionName TEXT,
    electionName2 TEXT,
    electionArea TEXT,
    name TEXT,
    type TEXT,
    pdfFileName TEXT,
    csvFileName TEXT,
    zipFileName TEXT,
    isBackend INTEGER,
    downloadPdf TEXT,
    downloadCsv TEXT,
    downloadZip TEXT,
    updatedDate TEXT
);

-- Table account_history --
CREATE TABLE IF NOT EXISTS account_history (
    id INTEGER PRIMARY KEY,
    path TEXT,
    accountNumber TEXT,
    accountType TEXT,
    yearOrSerial INTEGER,
    version INTEGER,
    electionYear INTEGER,
    electionName TEXT,
    electionName2 TEXT,
    electionArea TEXT,
    name TEXT,
    type TEXT,
    pdfFileName TEXT,
    csvFileName TEXT,
    zipFileName TEXT,
    isBackend INTEGER,
    downloadPdf TEXT,
    downloadCsv TEXT,
    downloadZip TEXT,
    versionedDate TEXT
);

-- Table election --
CREATE TABLE IF NOT EXISTS election (
    id TEXT PRIMARY KEY,
    path TEXT,
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
    downloadZip TEXT,
    updatedDate TEXT
);

-- Table account --
CREATE TABLE IF NOT EXISTS account (
    id TEXT PRIMARY KEY,
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
    updatedDate TEXT
);

-- Table account_history --
CREATE TABLE IF NOT EXISTS account_history (
    history_id INTEGER PRIMARY KEY,
    id INTEGER,
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

-- Table party --
CREATE TABLE IF NOT EXISTS party (
    id TEXT PRIMARY KEY,
    path TEXT,
    accountNumber TEXT,
    accountType TEXT,
    yearOrSerial INTEGER,
    version INTEGER,
    name TEXT,
    politicalPartyCode, INTEGER,
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

-- Table party_history --
CREATE TABLE IF NOT EXISTS party_history (
    history_id INTEGER PRIMARY KEY,
    id INTEGER,
    path TEXT,
    accountNumber TEXT,
    accountType TEXT,
    yearOrSerial INTEGER,
    version INTEGER,
    name TEXT,
    politicalPartyCode, INTEGER,
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

-- Table record --
CREATE TABLE IF NOT EXISTS record (
    id TEXT PRIMARY KEY,
    election_id TEXT, --relate to election.id
    account_id TEXT, --relate to account.id
    party_id TEXT, --relate to party.id
    name TEXT,
    electionName TEXT,
    yearOrSerial INTEGER,
    transactionDate TEXT,
    typeCode TEXT,
    type TEXT,
    donor TEXT,
    donorIdentifier TEXT,
    receivedAmount INTEGER,
    donationAmount INTEGER,
    payType TEXT,
    saveAccountDate TEXT,
    returnOrPaytrs TEXT,
    donationUse TEXT,
    isMoney INTEGER,
    donorAddress TEXT,
    tel TEXT,
    exposeRemark TEXT,
    rpIntraName TEXT,
    rpIntraTitle TEXT,
    rpPartyName TEXT,
    rpPartyTitle TEXT,
    rpRelationStr TEXT,
    diffVersionStr TEXT,
    updateDatetimeB TEXT,
    updatedDate TEXT
);

-- Table record_history --
CREATE TABLE IF NOT EXISTS record_history (
    history_id INTEGER PRIMARY KEY,
    id TEXT,
    election_id TEXT, --relate to election.id
    account_id TEXT, --relate to account.id
    party_id TEXT, --relate to party.id
    name TEXT,
    electionName TEXT,
    yearOrSerial INTEGER,
    transactionDate TEXT,
    typeCode TEXT,
    type TEXT,
    donor TEXT,
    donorIdentifier TEXT,
    receivedAmount INTEGER,
    donationAmount INTEGER,
    payType TEXT,
    saveAccountDate TEXT,
    returnOrPaytrs TEXT,
    donationUse TEXT,
    isMoney INTEGER,
    donorAddress TEXT,
    tel TEXT,
    exposeRemark TEXT,
    rpIntraName TEXT,
    rpIntraTitle TEXT,
    rpPartyName TEXT,
    rpPartyTitle TEXT,
    rpRelationStr TEXT,
    diffVersionStr TEXT,
    updateDatetimeB TEXT,
    versionedDate TEXT
);

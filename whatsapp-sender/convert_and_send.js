const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');
const { default: makeWASocket, useSingleFileAuthState, useSingleFileLegacyAuthState } = require('@adiwajshing/baileys');

console.log(typeof useSingleFileAuthState); // should print 'function'

// Convert CSV to Excel
const csvPath = path.join(__dirname, '../downloads/jobs_backup.csv');
const excelPath = path.join(__dirname, '../downloads/jobs_backup.xlsx');
const workbook = XLSX.readFile(csvPath, { type: 'file', codepage: 65001 });
XLSX.writeFile(workbook, excelPath);

// Send Excel via WhatsApp
const { state, saveState } = useSingleFileLegacyAuthState('./auth_info_baileys/state.json');
async function sendExcelFile(jid, filePath) {
    const sock = makeWASocket({ auth: state });
    sock.ev.on('creds.update', saveState);

    // Wait for connection
    await new Promise(resolve => setTimeout(resolve, 5000));

    const buffer = fs.readFileSync(filePath);
    await sock.sendMessage(jid, {
        document: buffer,
        fileName: 'jobs_backup.xlsx',
        mimetype: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });

    console.log('File sent!');
    process.exit(0);
}
const recipient = '082392042422@s.whatsapp.net';
sendExcelFile(recipient, excelPath);

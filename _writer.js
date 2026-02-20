const fs = require('fs');
const base = 'C:/Users/Local user/Music/aquarium-management/aquarium_management';

// Each file content is stored as base64 in a JSON object
const fileData = JSON.parse(fs.readFileSync(base + '/_filedata.json', 'utf8'));

for (const [filename, b64content] of Object.entries(fileData)) {
    const filepath = base + '/' + filename;
    const dir = require('path').dirname(filepath);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    fs.writeFileSync(filepath, Buffer.from(b64content, 'base64').toString('utf8'));
    console.log('Written: ' + filename);
}

// Cleanup
fs.unlinkSync(base + '/_filedata.json');
fs.unlinkSync(base + '/_writer.js');
console.log('All done. Cleanup complete.');

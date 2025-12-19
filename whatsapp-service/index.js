const { Client, LocalAuth } = require('whatsapp-web.js');
// const qrcode = require('qrcode-terminal'); // Removed for web integration
const express = require('express');
const bodyParser = require('body-parser');
const cors = require('cors');

const app = express();
const port = 3001;

app.use(cors());
app.use(bodyParser.json());

// State variables
let qrCode = null;
let clientStatus = 'INITIALIZING'; // INITIALIZING, WAITING_FOR_QR, READY, AUTHENTICATED

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on('qr', (qr) => {
    console.log('QR RECEIVED (Exposed via API)');
    qrCode = qr;
    clientStatus = 'WAITING_FOR_QR';
    // qrcode.generate(qr, { small: true }); // Disabled terminal output
});

client.on('ready', () => {
    console.log('Client is ready!');
    clientStatus = 'READY';
    qrCode = null; // Clear QR code when connected
});

client.on('authenticated', () => {
    console.log('AUTHENTICATED');
    clientStatus = 'AUTHENTICATED';
    qrCode = null;
});

client.on('auth_failure', msg => {
    console.error('AUTHENTICATION FAILURE', msg);
    clientStatus = 'AUTH_FAILURE';
});

client.on('disconnected', (reason) => {
    console.log('Client was disconnected', reason);
    clientStatus = 'INITIALIZING'; // Explicitly set to initializing
    client.initialize(); // Auto reconnect/reinit to get new QR
});

client.initialize();

// New Endpoint: Check Status & Get QR
app.get('/status', (req, res) => {
    res.json({
        status: clientStatus,
        qr: qrCode
    });
});

// New Endpoint: Logout
// New Endpoint: Logout
app.post('/logout', async (req, res) => {
    try {
        // Attempt graceful logout
        if (clientStatus === 'AUTHENTICATED' || clientStatus === 'READY') {
            await client.logout();
        }
    } catch (error) {
        console.error('Graceful logout failed, forcing reset:', error.message);
    } finally {
        // Force reset regardless of logout success/failure
        try {
            clientStatus = 'INITIALIZING';
            qrCode = null;
            await client.destroy();
            await client.initialize();

            // Allow some time for initialize to start
            res.json({ status: 'success', message: 'Logged out and service reset' });
        } catch (resetError) {
            console.error('Hard reset failed:', resetError);
            res.status(500).json({ status: 'error', message: 'Critical error during reset' });
        }
    }
});

app.post('/send', async (req, res) => {
    const { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({ status: 'error', message: 'Number and message are required' });
    }

    try {
        // Format number to ID (e.g., 628123456789@c.us)
        // Removing non-digits just in case
        let formattedNumber = number.replace(/\D/g, '');

        // Basic check for ID format, assuming user sends country code
        if (!formattedNumber.endsWith('@c.us')) {
            formattedNumber = `${formattedNumber}@c.us`;
        }

        const isRegistered = await client.isRegisteredUser(formattedNumber);
        if (!isRegistered) {
            return res.status(404).json({ status: 'error', message: 'Number is not registered on WhatsApp' });
        }

        await client.sendMessage(formattedNumber, message);
        res.json({ status: 'success', message: 'Message sent successfully' });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ status: 'error', message: 'Failed to send message' });
    }
});

app.listen(port, () => {
    console.log(`WhatsApp service listening on port ${port}`);
});

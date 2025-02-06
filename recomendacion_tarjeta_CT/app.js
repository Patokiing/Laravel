const express = require('express');
const bodyParser = require('body-parser');
const recommendRouter = require('./recommend');

const app = express();
const port = 3000;

app.use(bodyParser.json());
app.use(express.static('public'));

app.use('/recommend', recommendRouter);  // Define el enrutador para las recomendaciones

app.listen(port, () => {
    console.log(`Servidor iniciado en http://localhost:${port}`);
});

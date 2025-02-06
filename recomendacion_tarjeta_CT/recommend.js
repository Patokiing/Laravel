const express = require('express');
const fs = require('fs');
const csv = require('csv-parser');
const Recommender = require('./rl_model');
const { Parser } = require('json2csv');

const router = express.Router();
const stateSpace = 4;  // Número de preferencias del cliente
const actionSpace = 5;  // Número de tarjetas de crédito
const recommender = new Recommender(stateSpace, actionSpace);

let clientes = [];
let tarjetas = [];

// Función para validar datos de clientes
const validateCliente = (row) => {
    return row.id && row.nombre_completo && !isNaN(row.puntos) && !isNaN(row.cashback) && !isNaN(row.premios) && !isNaN(row.descuentos);
};

// Función para validar datos de tarjetas
const validateTarjeta = (row) => {
    return row.nombre && !isNaN(row.puntos) && !isNaN(row.cashback) && !isNaN(row.premios) && !isNaN(row.descuentos);
};

// Carga datos de clientes desde el archivo CSV
fs.createReadStream('clientes.csv')
    .pipe(csv())
    .on('data', (row) => {
        if (validateCliente(row)) {
            clientes.push({
                id: row.id,
                nombre_completo: row.nombre_completo,
                preferences: [row.puntos, row.cashback, row.premios, row.descuentos].map(Number),
            });
        }
    })
    .on('end', () => {
        console.log('Datos de clientes cargados:', clientes);
    });

// Carga datos de tarjetas desde el archivo CSV
fs.createReadStream('tarjetas.csv')
    .pipe(csv())
    .on('data', (row) => {
        if (validateTarjeta(row)) {
            tarjetas.push({
                nombre: row.nombre,
                beneficios: [row.puntos, row.cashback, row.premios, row.descuentos].map(Number),
            });
        }
    })
    .on('end', () => {
        console.log('Datos de tarjetas cargados:', tarjetas);
    });

// Ruta para obtener información del cliente
router.get('/cliente/:id', (req, res) => {
    const cliente = clientes.find(c => c.id === req.params.id);
    if (!cliente) {
        return res.status(404).json({ error: 'Cliente no encontrado' });
    }

    const tarjetaActual = tarjetas.find(t => t.beneficios.every((val, index) => val === cliente.preferences[index]));

    res.json({
        cliente: cliente,
        tarjetaActual: tarjetaActual ? tarjetaActual.nombre : 'Sin Tarjeta asignada'
    });
});

// Ruta para recomendar una tarjeta basada en nuevas preferencias
router.post('/recommend', (req, res) => {
    const { id, preferences } = req.body;

    console.log('ID recibido:', id);
    console.log('Preferencias recibidas:', preferences);

    if (!id || !Array.isArray(preferences) || preferences.length !== stateSpace) {
        return res.status(400).json({ error: 'Datos inválidos' });
    }

    // Prepara datos de entrenamiento
    const states = clientes.map(c => c.preferences);
    const actions = clientes.map(c => {
        const actionArray = new Array(actionSpace).fill(0);
        const cardIndex = tarjetas.findIndex(t => t.beneficios.every((val, index) => val === c.preferences[index]));
        if (cardIndex !== -1) {
            actionArray[cardIndex] = 1;
        }
        return actionArray;
    });

    console.log('States:', states);
    console.log('Actions:', actions);

    // Entrena el modelo
    recommender.trainModel(states, actions, 150, 32).then(() => {
        // Realiza la recomendación utilizando las nuevas preferencias
        console.log('Nuevas preferencias para recomendación:', preferences);
        const recommendationIndex = recommender.recommend(preferences);
        const recommendedCard = tarjetas[recommendationIndex];

        console.log('Tarjeta recomendada:', recommendedCard.nombre);

        res.json({ recommendedCard: recommendedCard.nombre });
    }).catch(err => res.status(500).json({ error: err.message }));
});

// Ruta para asignar la tarjeta recomendada al cliente
router.post('/asignar', (req, res) => {
    const { id, tarjeta } = req.body;

    const cliente = clientes.find(c => c.id === id);
    const tarjetaRecomendada = tarjetas.find(t => t.nombre === tarjeta);

    if (!cliente || !tarjetaRecomendada) {
        return res.status(400).json({ error: 'Cliente o tarjeta no encontrada' });
    }

    // Actualiza las preferencias del cliente con los beneficios de la tarjeta
    cliente.preferences = tarjetaRecomendada.beneficios;

    const updatedClientData = clientes.map(c => ({
        id: c.id,
        nombre_completo: c.nombre_completo,
        puntos: c.preferences[0],
        cashback: c.preferences[1],
        premios: c.preferences[2],
        descuentos: c.preferences[3]
    }));

    const json2csvParser = new Parser({ fields: ['id', 'nombre_completo', 'puntos', 'cashback', 'premios', 'descuentos'] });
    const csvData = json2csvParser.parse(updatedClientData);

    fs.writeFileSync('clientes.csv', csvData);

    res.json({ message: 'Tarjeta asignada correctamente' });
});

module.exports = router;

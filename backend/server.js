import express from 'express';
import dotenv from 'dotenv';
import { createClient } from '@supabase/supabase-js';

dotenv.config();

const app = express();
const port = 3000;

// Supabase client
const supabase = createClient(
  process.env.SUPABASE_URL,
  process.env.SUPABASE_SERVICE_ROLE_KEY
);

// Middleware para parsear JSON
app.use(express.json());

// GET /ping – lista todas las prendas
app.get('/ping', async (req, res) => {
  const { data, error } = await supabase.from('prendas').select('*');

  if (error) {
    return res.status(500).json({ error: error.message });
  }

  res.json({ success: true, data });
});

// POST /prendas – inserta nueva prenda (sin precio)
app.post('/prendas', async (req, res) => {
  const { nombre, tipo, talla, color } = req.body;

  if (!nombre || !tipo || !talla) {
    return res.status(400).json({
      error: 'Faltan campos obligatorios: nombre, tipo o talla',
    });
  }

  const { data, error } = await supabase
    .from('prendas')
    .insert([{ nombre, tipo, talla, color }]);

  if (error) {
    return res.status(500).json({ error: error.message });
  }

  res.status(201).json({ success: true, data });
});

app.listen(port, () => {
  console.log(`Servidor corriendo en http://localhost:${port}`);
});

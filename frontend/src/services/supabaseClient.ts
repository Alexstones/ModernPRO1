import { createClient } from '@supabase/supabase-js'

const supabaseUrl = 'https://tzchczbqhpvqlbxbpqvd.supabase.co' // ← tu URL de Supabase
const supabaseAnonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InR6Y2hjemJxaHB2cWxieGJwcXZkIiwicm9sZSI6ImFub24iLCJpYXQiOjE2OTk3NzY0NDMsImV4cCI6MjAxNTM1MjQ0M30.pMkN5nmFMIaGPGNdAznjMoyAkK_GQJKziKxLYM6_H3Q' // ← tu anon key pública

export const supabase = createClient(supabaseUrl, supabaseAnonKey)

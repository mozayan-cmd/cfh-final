# FishBiz Mobile - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a mobile-first fishing business management webapp with React + Tailwind + Express + Prisma

**Architecture:** Monorepo with separate frontend/backend - Express REST API + React SPA with touch-first responsive UI

**Tech Stack:** React 18, Tailwind CSS 4, Vite, Node.js, Express, Prisma, SQLite/PostgreSQL

---

## Phase 1: Project Foundation

### Task 1: Initialize Project Structure

**Files:**
- Create: `fishbiz-mobile/package.json`
- Create: `fishbiz-mobile/vite.config.js`
- Create: `fishbiz-mobile/index.html`
- Create: `fishbiz-mobile/.env`
- Create: `fishbiz-mobile/prisma/schema.prisma`

- [ ] **Step 1: Create project directory and package.json**

```bash
mkdir fishbiz-mobile
cd fishbiz-mobile
npm init -y
```

- [ ] **Step 2: Install dependencies**

```bash
npm install react@18 react-dom@18 react-router-dom@6 zustand @tanstack/react-query react-hook-form @hookform/resolvers axios clsx tailwind-merge lucide-react
npm install -D vite@5 @vitejs/plugin-react tailwindcss@4 postcss autoprefixer prisma @prisma/client concurrently
```

- [ ] **Step 3: Create vite.config.js**

```javascript
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    port: 5173,
    proxy: {
      '/api': 'http://localhost:3000'
    }
  }
})
```

- [ ] **Step 4: Create index.html**

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="theme-color" content="#2563eb">
  <title>FishBiz Mobile</title>
</head>
<body>
  <div id="root"></div>
  <script type="module" src="/src/main.jsx"></script>
</body>
</html>
```

- [ ] **Step 5: Create tailwind config**

```javascript
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        primary: '#2563eb',
        secondary: '#64748b'
      },
      spacing: {
        'nav': '64px',
        'header': '56px',
        'safe-bottom': '80px'
      }
    }
  }
}
```

---

### Task 2: Setup Prisma Database Schema

**Files:**
- Create: `fishbiz-mobile/prisma/schema.prisma`

- [ ] **Step 1: Initialize Prisma**

```bash
npx prisma init --datasource sqlite
```

- [ ] **Step 2: Write schema.prisma**

```prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "sqlite"
  url      = env("DATABASE_URL")
}

model User {
  id        String   @id @default(uuid())
  email     String  @unique
  phone     String?
  password  String
  name      String
  role      String   @default("user")
  createdAt DateTime @default(now())
  updatedAt DateTime @updatedAt
  boats     Boat[]
  buyers    Buyer[]
  landings  Landing[]
  invoices  Invoice[]
  receipts  Receipt[]
  expenses  Expense[]
  payments  Payment[]
  loans     Loan[]
}

model Boat {
  id                 String    @id @default(uuid())
  userId             String
  user               User      @relation(fields: [userId], references: [id])
  name              String
  registrationNumber String?
  ownerName         String?
  capacity          Float?
  status            String    @default("active")
  createdAt         DateTime  @default(now())
  updatedAt         DateTime  @updatedAt
  landings         Landing[]
}

model Buyer {
  id        String   @id @default(uuid())
  userId    String
  user      User     @relation(fields: [userId], references: [id])
  name      String
  phone     String?
  email     String?
  address   String?
  notes     String?
  createdAt DateTime @default(now())
  updatedAt DateTime @updatedAt
  landings  Landing[]
  invoices  Invoice[]
  receipts  Receipt[]
}

model Landing {
  id            String   @id @default(uuid())
  userId        String
  user          User     @relation(fields: [userId], references: [id])
  boatId        String
  boat          Boat     @relation(fields: [boatId], references: [id])
  buyerId       String
  buyer         Buyer    @relation(fields: [buyerId], references: [id])
  date         DateTime
  grossWeight  Float
  pricePerKg   Float
  grossValue   Float
  status      String   @default("pending")
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
  invoiceLandings InvoiceLanding[]
}

model Invoice {
  id            String   @id @default(uuid())
  userId        String
  user          User     @relation(fields: [userId], references: [id])
  invoiceNumber String  @unique
  date          DateTime
  buyerId       String
  buyer         Buyer    @relation(fields: [buyerId], references: [id])
  totalAmount   Float
  status        String   @default("draft")
  createdAt    DateTime @default(now())
  updatedAt    DateTime @updatedAt
  invoiceLandings InvoiceLanding[]
  receiptInvoices ReceiptInvoice[]
}

model InvoiceLanding {
  id         String  @id @default(uuid())
  invoiceId  String
  invoice    Invoice @relation(fields: [invoiceId], references: [id])
  landingId  String
  landing   Landing @relation(fields: [landingId], references: [id])
}

model Receipt {
  id             String   @id @default(uuid())
  userId         String
  user           User     @relation(fields: [userId], references: [id])
  receiptNumber  String   @unique
  date           DateTime
  buyerId        String
  buyer          Buyer    @relation(fields: [buyerId], references: [id])
  amount         Float
  paymentMethod  String
  createdAt      DateTime @default(now())
  updatedAt     DateTime @updatedAt
  receiptInvoices ReceiptInvoice[]
}

model ReceiptInvoice {
  id         String  @id @default(uuid())
  receiptId String
  receipt   Receipt @relation(fields: [receiptId], references: [id])
  invoiceId String
  invoice   Invoice @relation(fields: [invoiceId], references: [id])
  amount    Float
}

model Expense {
  id            String   @id @default(uuid())
  userId        String
  user          User     @relation(fields: [userId], references: [id])
  date          DateTime
  description   String
  amount        Float
  category      String
  paymentMethod String?
  notes        String?
  createdAt    DateTime @default(now())
  updatedAt    DateTime @updatedAt
}

model Payment {
  id            String   @id @default(uuid())
  userId        String
  user          User     @relation(fields: [userId], references: [id])
  date          DateTime
  amount        Float
  type          String
  description   String?
  paymentMethod String
  createdAt    DateTime @default(now())
  updatedAt    DateTime @updatedAt
  paymentAllocations PaymentAllocation[]
}

model PaymentAllocation {
  id          String  @id @default(uuid())
  paymentId   String
  payment     Payment @relation(fields: [paymentId], references: [id])
  expenseId   String?
  expense     Expense? @relation(fields: [expenseId], references: [id])
  loanId     String?
  loan       Loan?   @relation(fields: [loanId], references: [id])
  amount     Float
}

model Loan {
  id            String   @id @default(uuid())
  userId        String
  user          User     @relation(fields: [userId], references: [id])
  loanNumber    String  @unique
  source        String
  principal    Float
  interestRate Float
  startDate   DateTime
  tenure      Int
  status      String   @default("active")
  createdAt   DateTime @default(now())
  updatedAt   DateTime @updatedAt
  payments    PaymentAllocation[]
}
```

- [ ] **Step 3: Run migration**

```bash
npx prisma migrate dev --name init
```

- [ ] **Step 4: Generate client**

```bash
npx prisma generate
```

---

## Phase 2: Backend API

### Task 3: Express Server Setup

**Files:**
- Create: `fishbiz-mobile/server/index.js`
- Create: `fishbiz-mobile/server/routes/auth.js`
- Create: `fishbiz-mobile/server/middleware/auth.js`

- [ ] **Step 1: Install server deps**

```bash
npm install express cors dotenv jsonwebtoken bcryptjs uuid
npm install -D nodemon
```

- [ ] **Step 2: Create server/index.js**

```javascript
const express = require('express')
const cors = require('cors')
require('dotenv').config()

const app = express()
app.use(cors())
app.use(express.json())

app.get('/api/health', (req, res) => {
  res.json({ status: 'ok' })
})

const PORT = process.env.PORT || 3000
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`)
})
```

- [ ] **Step 3: Add startup scripts to package.json**

```json
{
  "scripts": {
    "dev": "concurrently \"npm run dev:server\" \"npm run dev:client\"",
    "dev:server": "nodemon server/index.js",
    "dev:client": "vite",
    "build": "vite build",
    "server": "node server/index.js"
  }
}
```

---

### Task 4: Auth Routes (Phone OTP + Email/Password)

**Files:**
- Modify: `fishbiz-mobile/server/index.js`
- Create: `fishbiz-mobile/server/routes/auth.js`
- Create: `fishbiz-mobile/server/middleware/auth.js`

- [ ] **Step 1: Add auth routes to server/index.js**

```javascript
const authRoutes = require('./routes/auth')
app.use('/api/v1/auth', authRoutes)
```

- [ ] **Step 2: Create auth routes**

```javascript
const express = require('express')
const router = express.Router()
const jwt = require('jsonwebtoken')
const bcrypt = require('bcryptjs')
const { PrismaClient } = require('@prisma/client')
const { v4: uuidv4 } = require('uuid')

const prisma = new PrismaClient()
const JWT_SECRET = process.env.JWT_SECRET || 'dev-secret-key'

// Phone OTP - send (simulated)
router.post('/phone/send-otp', async (req, res) => {
  const { phone } = req.body
  if (!phone) return res.status(400).json({ error: 'Phone required' })
  const otp = Math.floor(100000 + Math.random() * 900000).toString()
  // In production: send SMS via provider
  console.log(`OTP for ${phone}: ${otp}`)
  res.json({ message: 'OTP sent', otp }) // Remove otp in production
})

// Phone OTP - verify
router.post('/phone/verify', async (req, res) => {
  const { phone, otp } = req.body
  // Verify OTP (simplified)
  if (otp.length !== 6) return res.status(400).json({ error: 'Invalid OTP' })
  let user = await prisma.user.findFirst({ where: { phone } })
  if (!user) {
    user = await prisma.user.create({
      data: { phone, name: phone, password: uuidv4(), role: 'user' }
    })
  }
  const token = jwt.sign({ userId: user.id }, JWT_SECRET, { expiresIn: '7d' })
  res.json({ token, user: { id: user.id, name: user.name, phone: user.phone } })
})

// Email/Password login
router.post('/login', async (req, res) => {
  const { email, password } = req.body
  const user = await prisma.user.findUnique({ where: { email } })
  if (!user) return res.status(401).json({ error: 'Invalid credentials' })
  const valid = await bcrypt.compare(password, user.password)
  if (!valid) return res.status(401).json({ error: 'Invalid credentials' })
  const token = jwt.sign({ userId: user.id }, JWT_SECRET, { expiresIn: '7d' })
  res.json({ token, user: { id: user.id, name: user.name, email: user.email } })
})

// Register
router.post('/register', async (req, res) => {
  const { email, password, name, phone } = req.body
  const hashed = await bcrypt.hash(password, 10)
  try {
    const user = await prisma.user.create({
      data: { email, password: hashed, name, phone }
    })
    const token = jwt.sign({ userId: user.id }, JWT_SECRET, { expiresIn: '7d' })
    res.json({ token, user: { id: user.id, name: user.name, email: user.email } })
  } catch (e) {
    res.status(400).json({ error: 'Email already exists' })
  }
})

module.exports = router
```

- [ ] **Step 3: Create auth middleware**

```javascript
const jwt = require('jsonwebtoken')
const { PrismaClient } = require('@prisma/client')
const JWT_SECRET = process.env.JWT_SECRET || 'dev-secret-key'
const prisma = new PrismaClient()

const auth = async (req, res, next) => {
  const token = req.headers.authorization?.replace('Bearer ', '')
  if (!token) return res.status(401).json({ error: 'No token' })
  try {
    const decoded = jwt.verify(token, JWT_SECRET)
    const user = await prisma.user.findUnique({ where: { id: decoded.userId } })
    if (!user) return res.status(401).json({ error: 'User not found' })
    req.user = user
    next()
  } catch (e) {
    res.status(401).json({ error: 'Invalid token' })
  }
}

module.exports = auth
```

---

### Task 5: CRUD Routes

**Files:**
- Create: `boatRoutes.js`, `buyerRoutes.js`, `landingRoutes.js`, `invoiceRoutes.js`, `receiptRoutes.js`, `expenseRoutes.js`, `paymentRoutes.js`, `loanRoutes.js`

- [ ] **Step 1: Create boats route**

```javascript
const express = require('express')
const router = express.Router()
const auth = require('../middleware/auth')
const { PrismaClient } = require('@prisma/client')
const prisma = new PrismaClient()

// List
router.get('/', auth, async (req, res) => {
  const boats = await prisma.boat.findMany({ where: { userId: req.user.id } })
  res.json(boats)
})

// Create
router.post('/', auth, async (req, res) => {
  const boat = await prisma.boat.create({
    data: { ...req.body, userId: req.user.id }
  })
  res.json(boat)
})

// Get one
router.get('/:id', auth, async (req, res) => {
  const boat = await prisma.boat.findFirst({ 
    where: { id: req.params.id, userId: req.user.id } 
  })
  res.json(boat)
})

// Update
router.put('/:id', auth, async (req, res) => {
  const boat = await prisma.boat.update({
    where: { id: req.params.id },
    data: req.body
  })
  res.json(boat)
})

// Delete
router.delete('/:id', auth, async (req, res) => {
  await prisma.boat.delete({ where: { id: req.params.id } })
  res.json({ success: true })
})

module.exports = router
```

(Similar pattern for all other routes - buyers, landings, invoices, receipts, expenses, payments, loans)

- [ ] **Step 2: Register all routes in server/index.js**

```javascript
const boats = require('./routes/boats')
const buyers = require('./routes/buyers')
const landings = require('./routes/landings')
// ... etc

app.use('/api/v1/boats', boats)
app.use('/api/v1/buyers', buyers)
app.use('/api/v1/landings', landings)
// ... etc
```

---

## Phase 3: Frontend

### Task 6: Core UI Components

**Files:**
- Create: `src/components/ui/Button.jsx`
- Create: `src/components/ui/Input.jsx`
- Create: `src/components/ui/Card.jsx`
- Create: `src/components/layout/Header.jsx`
- Create: `src/components/layout/BottomNav.jsx`
- Create: `src/components/layout/Layout.jsx`

- [ ] **Step 1: Create Button component**

```jsx
import { clsx } from 'clsx'

export function Button({ children, variant = 'primary', size = 'md', className, ...props }) {
  const base = 'flex items-center justify-center font-medium rounded-lg transition-transform active:scale-98'
  const variants = {
    primary: 'bg-blue-600 text-white hover:bg-blue-700',
    secondary: 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    danger: 'bg-red-600 text-white hover:bg-red-700'
  }
  const sizes = {
    sm: 'h-8 px-3 text-sm',
    md: 'h-12 px-4 text-base',
    lg: 'h-14 px-6 text-lg'
  }
  return (
    <button className={clsx(base, variants[variant], sizes[size], className)} {...props}>
      {children}
    </button>
  )
}
```

- [ ] **Step 2: Create Input component**

```jsx
export function Input({ label, error, ...props }) {
  return (
    <div className="space-y-1">
      {label && <label className="text-sm font-medium text-gray-700">{label}</label>}
      <input 
        className="w-full h-12 px-4 border rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
        {...props}
      />
      {error && <p className="text-sm text-red-500">{error}</p>}
    </div>
  )
}
```

- [ ] **Step 3: Create Card component**

```jsx
import { clsx } from 'clsx'

export function Card({ children, className, onClick, ...props }) {
  return (
    <div 
      className={clsx(
        'bg-white rounded-lg shadow-sm p-4',
        onClick && 'cursor-pointer active:scale-99 transition-transform',
        className
      )}
      onClick={onClick}
      {...props}
    >
      {children}
    </div>
  )
}
```

- [ ] **Step 4: Create Header component**

```jsx
import { Menu, Bell } from 'lucide-react'

export function Header({ title }) {
  return (
    <header className="fixed top-0 left-0 right-0 h-14 bg-white border-b flex items-center px-4 z-50">
      <h1 className="text-lg font-semibold">{title}</h1>
    </header>
  )
}
```

- [ ] **Step 5: Create BottomNav component**

```jsx
import { Home, Wallet, Anchor, ChartBar, Menu } from 'lucide-react'
import { NavLink } from 'react-router-dom'

const tabs = [
  { to: '/', icon: Home, label: 'Home' },
  { to: '/transactions', icon: Wallet, label: 'Transactions' },
  { to: '/boats', icon: Anchor, label: 'Boats' },
  { to: '/reports', icon: ChartBar, label: 'Reports' },
  { to: '/menu', icon: Menu, label: 'Menu' }
]

export function BottomNav() {
  return (
    <nav className="fixed bottom-0 left-0 right-0 h-16 bg-white border-t flex items-center justify-around z-50 safe-bottom">
      {tabs.map(({ to, icon: Icon, label }) => (
        <NavLink 
          key={to} 
          to={to}
          className={({ isActive }) => 
            `flex flex-col items-center justify-center w-full h-full ${isActive ? 'text-blue-600' : 'text-gray-500'}`
          }
        >
          <Icon size={24} />
          <span className="text-xs mt-1">{label}</span>
        </NavLink>
      ))}
    </nav>
  )
}
```

- [ ] **Step 6: Create Layout component**

```jsx
import { Header } from './Header'
import { BottomNav } from './BottomNav'

export function Layout({ title, children }) {
  return (
    <div className="min-h-screen bg-gray-50">
      <Header title={title} />
      <main className="pt-14 pb-20 px-4">{children}</main>
      <BottomNav />
    </div>
  )
}
```

---

### Task 7: App Entry Points

**Files:**
- Create: `src/main.jsx`
- Create: `src/App.jsx`
- Create: `src/api/client.js`
- Create: `src/store/index.js`
- Create: `src/index.css`

- [ ] **Step 1: Create main.jsx**

```jsx
import React from 'react'
import ReactDOM from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { BrowserRouter } from 'react-router-dom'
import App from './App'
import './index.css'

const queryClient = new QueryClient()

ReactDOM.createRoot(document.getElementById('root')).render(
  <React.StrictMode>
    <QueryClientProvider client={queryClient}>
      <BrowserRouter>
        <App />
      </BrowserRouter>
    </QueryClientProvider>
  </React.StrictMode>
)
```

- [ ] **Step 2: Create index.css**

```css
@import "tailwindcss"

* {
  -webkit-tap-highlight-color: transparent;
}

body {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  touch-action: manipulation;
}

input, button, select, textarea {
  font-size: 16px;
}
```

- [ ] **Step 3: Create App.jsx**

```jsx
import { Routes, Route, Navigate } from 'react-router-dom'
import { useStore } from './store'
import { Layout } from './components/layout/Layout'
import { Login } from './screens/auth/Login'
import { Dashboard } from './screens/dashboard/Dashboard'
import { Transactions } from './screens/transactions/Transactions'
import { Boats } from './screens/boats/Boats'
import { Reports } from './screens/reports/Reports'
import { Menu } from './screens/menu/Menu'

function PrivateRoute({ children }) {
  const token = useStore(s => s.token)
  return token ? children : <Navigate to="/login" />
}

export default function App() {
  return (
    <Routes>
      <Route path="/login" element={<Login />} />
      <Route path="/" element={<PrivateRoute><Layout title="FishBiz"><Dashboard /></Layout></PrivateRoute>} />
      <Route path="/transactions" element={<PrivateRoute><Layout title="Transactions"><Transactions /></Layout></PrivateRoute>} />
      <Route path="/boats" element={<PrivateRoute><Layout title="Boats"><Boats /></Layout></PrivateRoute>} />
      <Route path="/reports" element={<PrivateRoute><Layout title="Reports"><Reports /></Layout></PrivateRoute>} />
      <Route path="/menu" element={<PrivateRoute><Layout title="Menu"><Menu /></Layout></PrivateRoute>} />
    </Routes>
  )
}
```

- [ ] **Step 4: Create Zustand store**

```jsx
import { create } from 'zustand'
import { persist } from 'zustand/middleware'

export const useStore = create(persist(
  (set) => ({
    token: null,
    user: null,
    setAuth: (token, user) => set({ token, user }),
    logout: () => set({ token: null, user: null })
  }),
  { name: 'fishbiz-storage' }
))
```

- [ ] **Step 5: Create API client**

```jsx
import axios from 'axios'
import { useStore } from '../store'

const api = axios.create({
  baseURL: '/api/v1'
})

api.interceptors.request.use((config) => {
  const token = useStore.getState().token
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

export { api }
```

---

### Task 8: Screen Components

**Files:**
- Create: `src/screens/auth/Login.jsx`
- Create: `src/screens/dashboard/Dashboard.jsx`
- Create: `src/screens/transactions/Transactions.jsx`
- Create: `src/screens/boats/Boats.jsx`
- Create: `src/screens/reports/Reports.jsx`
- Create: `src/screens/menu/Menu.jsx`

- [ ] **Step 1: Create Login screen**

```jsx
import { useState } from 'react'
import { Button } from '../../components/ui/Button'
import { Input } from '../../components/ui/Input'
import { api } from '../../api'
import { useStore } from '../../store'

export function Login() {
  const [isPhone, setIsPhone] = useState(false)
  const [phone, setPhone] = useState('')
  const [otp, setOtp] = useState('')
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [error, setError] = useState('')
  const setAuth = useStore(s => s.setAuth)

  const handlePhoneLogin = async () => {
    try {
      const { data } = await api.post('/auth/phone/verify', { phone, otp })
      setAuth(data.token, data.user)
    } catch (e) {
      setError(e.response?.data?.error || 'Failed')
    }
  }

  const handleEmailLogin = async () => {
    try {
      const { data } = await api.post('/auth/login', { email, password })
      setAuth(data.token, data.user)
    } catch (e) {
      setError(e.response?.data?.error || 'Failed')
    }
  }

  return (
    <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
      <div className="w-full max-w-sm space-y-4">
        <h1 className="text-2xl font-bold text-center">FishBiz</h1>
        {isPhone ? (
          <div className="space-y-4">
            <Input 
              label="Phone" 
              value={phone} 
              onChange={e => setPhone(e.target.value)}
              placeholder="+1234567890"
            />
            <Input 
              label="OTP" 
              value={otp} 
              onChange={e => setOtp(e.target.value)}
              placeholder="123456"
            />
            <Button onClick={handlePhoneLogin} className="w-full">Login</Button>
            <Button variant="secondary" onClick={() => setIsPhone(false)} className="w-full">
              Use Email instead
            </Button>
          </div>
        ) : (
          <div className="space-y-4">
            <Input 
              label="Email" 
              type="email"
              value={email} 
              onChange={e => setEmail(e.target.value)}
              placeholder="you@example.com"
            />
            <Input 
              label="Password" 
              type="password"
              value={password} 
              onChange={e => setPassword(e.target.value)}
            />
            <Button onClick={handleEmailLogin} className="w-full">Login</Button>
            <Button variant="secondary" onClick={() => setIsPhone(true)} className="w-full">
              Use Phone instead
            </Button>
          </div>
        )}
        {error && <p className="text-red-500 text-center">{error}</p>}
      </div>
    </div>
  )
}
```

- [ ] **Step 2: Create Dashboard screen**

```jsx
import { useQuery } from '@tanstack/react-query'
import { api } from '../../api'
import { Card } from '../../components/ui/Card'
import { Button } from '../../components/ui/Button'
import { Plus, TrendingUp, Wallet, Anchor } from 'lucide-react'
import { Link } from 'react-router-dom'

export function Dashboard() {
  const { data } = useQuery({ queryKey: ['dashboard'], queryFn: () => api.get('/dashboard').then(r => r.data) })

  return (
    <div className="space-y-4">
      <div className="grid grid-cols-2 gap-3">
        <Link to="/landings/new">
          <Card className="flex flex-col items-center py-4">
            <Plus className="text-blue-600 mb-2" size={24} />
            <span className="text-sm">New Landing</span>
          </Card>
        </Link>
        <Link to="/invoices/new">
          <Card className="flex flex-col items-center py-4">
            <Wallet className="text-green-600 mb-2" size={24} />
            <span className="text-sm">New Invoice</span>
          </Card>
        </Link>
        <Link to="/expenses/new">
          <Card className="flex flex-col items-center py-4">
            <TrendingUp className="text-red-600 mb-2" size={24} />
            <span className="text-sm">New Expense</span>
          </Card>
        </Link>
        <Link to="/payments/new">
          <Card className="flex flex-col items-center py-4">
            <Anchor className="text-purple-600 mb-2" size={24} />
            <span className="text-sm">New Payment</span>
          </Card>
        </Link>
      </div>

      {data?.summary && (
        <Card>
          <h2 className="font-semibold mb-2">Today's Summary</h2>
          <div className="space-y-2 text-sm">
            <div className="flex justify-between">
              <span>Landings:</span>
              <span>{data.summary.landings || 0}</span>
            </div>
            <div className="flex justify-between">
              <span>Value:</span>
              <span>{data.summary.landingValue || 0}</span>
            </div>
          </div>
        </Card>
      )}
    </div>
  )
}
```

- [ ] **Step 3: Create Transactions screen (template)**

```jsx
import { useState } from 'react'
import { useQuery } from '@tanstack/react-query'
import { api } from '../../api'
import { Card } from '../../components/ui/Card'
import { Button } from '../../components/ui/Button'
import { TabBar } from '../../components/ui/TabBar'
import { Plus } from 'lucide-react'

const tabs = ['Landings', 'Invoices', 'Receipts', 'Expenses', 'Payments', 'Loans']

export function Transactions() {
  const [tab, setTab] = useState(0)
  const { data, isLoading } = useQuery({ 
    queryKey: ['transactions', tabs[tab].toLowerCase()], 
    queryFn: () => api.get(`/${tabs[tab].toLowerCase()}`).then(r => r.data)
  })

  return (
    <div className="space-y-4">
      <TabBar tabs={tabs} active={tab} onChange={setTab} />
      
      <Link to={`/${tabs[tab].toLowerCase()}/new`}>
        <Button className="w-full flex items-center justify-center gap-2">
          <Plus size={20} /> Add {tabs[tab]}
        </Button>
      </Link>

      {isLoading ? (
        <div className="text-center py-8 text-gray-500">Loading...</div>
      ) : data?.length ? (
        <div className="space-y-2">
          {data.map(item => (
            <Card key={item.id}>
              <div className="flex justify-between">
                <div>
                  <div className="font-medium">{item.name || item.invoiceNumber || item.date}</div>
                  <div className="text-sm text-gray-500">{item.description || item.buyerId}</div>
                </div>
                <div className="text-right">
                  <div>{item.amount || item.totalAmount || item.grossValue}</div>
                  <div className="text-sm text-gray-500">{item.status}</div>
                </div>
              </div>
            </Card>
          ))}
        </div>
      ) : (
        <div className="text-center py-8 text-gray-500">No {tabs[tab].toLowerCase()} yet</div>
      )}
    </div>
  )
}
```

- [ ] **Step 4: Create remaining screens (simplified)**

Similar patterns for Boats, Buyers, Reports, Menu screens

---

## Phase 4: Forms & Detail Views

### Task 9: Form Components

### Task 10: Detail Views

---

## Execution

**Two execution options:**

**1. Subagent-Driven (recommended)** - Task 1 subagent per task, two-stage review between tasks, fast iteration

**2. Inline Execution** - Execute tasks in this session using executing-plans, batch execution with checkpoints for review

**Which approach?**
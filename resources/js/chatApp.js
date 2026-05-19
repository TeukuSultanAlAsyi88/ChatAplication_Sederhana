const STORAGE_KEY = 'chattatan_auth_v7'
const LEGACY_STORAGE_KEY = 'chattatan_auth_v6'
const OLDER_LEGACY_STORAGE_KEY = 'chattatan_auth_v5'
const SESSION_KEY = 'chattatan_current_user_v7'
const LEGACY_SESSION_KEY = 'chattatan_current_user_v6'
const OLDER_LEGACY_SESSION_KEY = 'chattatan_current_user_v5'
const channel = new BroadcastChannel('chattatan_realtime_v7')

let db = loadDatabase()
let currentUser = findSessionUser()
let currentView = 'login'
let activeChatId = null
let activeTab = 'chats'
let selectedPhoto = null
let selectedFile = null
let replyMessage = null

export function renderApp() {
  if (currentUser) {
    setPresence(true)
    renderMainApp()
  } else {
    renderAuthPage()
  }
}

function uid() {
  return crypto.randomUUID()
}

function createInitialDatabase() {
  const demoId = uid()
  const nabilaId = uid()
  const rakaId = uid()

  return {
    users: [
      { id: demoId, name: 'Demo User', email: 'demo@mail.com', phone: '081234567890', birthDate: '2002-01-01', password: 'demo123', status: 'Mahasiswa', bio: 'Mahasiswa', avatar: '', onlineVisible: true, lastSeenVisible: true },
      { id: nabilaId, name: 'Nabila Putri', email: 'nabila@student.id', phone: '08123000111', birthDate: '2001-05-12', password: 'nabila123', status: 'Mahasiswa', bio: 'Mahasiswa', avatar: '', onlineVisible: true, lastSeenVisible: true },
      { id: rakaId, name: 'Raka Pratama', email: 'raka@student.id', phone: '08123000222', birthDate: '2000-11-20', password: 'raka123', status: 'Mahasiswa', bio: 'Mahasiswa', avatar: '', onlineVisible: true, lastSeenVisible: true }
    ],
    contacts: [
      { id: uid(), ownerId: demoId, linkedUserId: nabilaId, name: 'Nabila Putri', email: 'nabila@student.id', phone: '08123000111', status: 'Mahasiswa', bio: 'Mahasiswa', avatar: '', onlineVisible: true, lastSeenVisible: true },
      { id: uid(), ownerId: demoId, linkedUserId: rakaId, name: 'Raka Pratama', email: 'raka@student.id', phone: '08123000222', status: 'Mahasiswa', bio: 'Mahasiswa', avatar: '', onlineVisible: true, lastSeenVisible: true }
    ],
    chats: [
      {
        id: uid(),
        type: 'private',
        name: 'Nabila Putri',
        members: [demoId, nabilaId],
        pinnedBy: [],
        archivedBy: [],
        unreadBy: {},
        messages: [
          makeMessage(nabilaId, 'Halo, progress chat application-nya sudah sampai mana?', { time: Date.now() - 500000, deliveredTo: [demoId], readBy: [demoId] }),
          makeMessage(demoId, 'Sudah ada login, register, contact, private chat, group chat, dan realtime simulation.', { time: Date.now() - 400000, reactions: ['👍'], deliveredTo: [nabilaId], readBy: [nabilaId] })
        ]
      },
      {
        id: uid(),
        type: 'group',
        name: 'Project Web Programming',
        members: [demoId, nabilaId, rakaId],
        pinnedBy: [],
        archivedBy: [],
        unreadBy: { [demoId]: 1 },
        messages: [
          makeMessage(rakaId, 'Jangan lupa demo realtime pakai dua tab browser.', { time: Date.now() - 250000, deliveredTo: [demoId, nabilaId], readBy: [] })
        ]
      }
    ],
    presence: {},
    settings: {}
  }
}

function loadDatabase() {
  const saved = localStorage.getItem(STORAGE_KEY)
  if (saved) return normalizeDatabase(JSON.parse(saved))

  const legacy = localStorage.getItem(LEGACY_STORAGE_KEY) || localStorage.getItem(OLDER_LEGACY_STORAGE_KEY)
  if (legacy) {
    const migrated = normalizeDatabase(JSON.parse(legacy))
    localStorage.setItem(STORAGE_KEY, JSON.stringify(migrated))
    return migrated
  }

  const initial = createInitialDatabase()
  localStorage.setItem(STORAGE_KEY, JSON.stringify(initial))
  return initial
}

function normalizeDatabase(data) {
  const normalized = { ...createInitialDatabase(), ...data }
  normalized.users = (normalized.users || []).map(user => ({ id: user.id || uid(), status: 'Mahasiswa', bio: user.bio || user.status || 'Mahasiswa', avatar: '', onlineVisible: true, lastSeenVisible: true, ...user }))

  const userByEmail = email => normalized.users.find(user => user.email === email)
  const userByName = name => normalized.users.find(user => user.name === name)

  normalized.contacts = (normalized.contacts || []).map(contact => {
    const linked = contact.linkedUserId
      ? normalized.users.find(user => user.id === contact.linkedUserId)
      : userByEmail(contact.email) || userByName(contact.name)

    return {
      id: contact.id || uid(),
      ownerId: contact.ownerId || null,
      linkedUserId: linked?.id || null,
      name: contact.name || linked?.name || 'Kontak',
      email: contact.email || linked?.email || '',
      phone: contact.phone || linked?.phone || '',
      status: contact.status || contact.bio || linked?.status || linked?.bio || '',
      bio: contact.status || contact.bio || linked?.status || linked?.bio || '',
      avatar: contact.avatar || linked?.avatar || ''
    }
  })

  normalized.chats = (normalized.chats || []).map(chat => {
    const members = (chat.members || []).map(member => {
      if (normalized.users.some(user => user.id === member)) return member
      return userByName(member)?.id || member
    }).filter(Boolean)

    return {
      id: chat.id || uid(),
      type: chat.type || 'private',
      name: chat.name || 'Chat',
      members,
      pinnedBy: chat.pinnedBy || (chat.pinned && normalized.users[0] ? [normalized.users[0].id] : []),
      archivedBy: chat.archivedBy || (chat.archived && normalized.users[0] ? [normalized.users[0].id] : []),
      unreadBy: chat.unreadBy || {},
      messages: (chat.messages || []).map(message => ({
        id: message.id || uid(),
        senderId: message.senderId || userByName(message.sender)?.id || members[0],
        text: message.text || decryptText(message.encryptedText) || '',
        encryptedText: message.encryptedText || encryptText(message.text || ''),
        encrypted: true,
        time: message.time || Date.now(),
        photo: message.photo || null,
        file: message.file || null,
        reply: message.reply || null,
        replyToId: message.replyToId || null,
        deliveredTo: message.deliveredTo || [],
        readBy: message.readBy || [],
        reactions: message.reactions || []
      }))
    }
  })

  normalized.presence = normalized.presence || {}
  normalized.settings = normalized.settings || {}
  return normalized
}

function saveDatabase() {
  localStorage.setItem(STORAGE_KEY, JSON.stringify(db))
}

function findSessionUser() {
  const saved = localStorage.getItem(SESSION_KEY) || localStorage.getItem(LEGACY_SESSION_KEY) || localStorage.getItem(OLDER_LEGACY_SESSION_KEY)
  if (!saved) return null
  const session = JSON.parse(saved)
  return db.users.find(user => user.id === session.id || user.email === session.email) || null
}

function setPresence(isOnline) {
  if (!currentUser) return
  db.presence[currentUser.id] = { online: isOnline, lastSeen: Date.now() }
  saveDatabase()
  channel.postMessage({ type: 'presence' })
}

window.addEventListener('beforeunload', () => setPresence(false))

function renderAuthPage() {
  document.body.classList.remove('dark')
  const app = document.getElementById('app')
  app.innerHTML = `
    <section class="auth-page">
      <div class="auth-card">
        <div class="brand">
          <div class="brand-logo">CC</div>
          <div>
            <h1>Chattatan</h1>
            <p>Chat sederhana untuk private, kontak, dan group.</p>
          </div>
        </div>
        <div class="auth-switch">
          <button id="loginSwitch" class="${currentView === 'login' ? 'active' : ''}">Login</button>
          <button id="registerSwitch" class="${currentView === 'register' ? 'active' : ''}">Register</button>
        </div>
        <div id="authForm"></div>
      </div>
    </section>
  `

  document.getElementById('loginSwitch').onclick = () => { currentView = 'login'; renderAuthPage() }
  document.getElementById('registerSwitch').onclick = () => { currentView = 'register'; renderAuthPage() }
  currentView === 'login' ? renderLoginForm() : renderRegisterForm()
}

function renderLoginForm() {
  document.getElementById('authForm').innerHTML = `
    <div class="form-group"><label>Email atau Nomor HP</label><input id="loginIdentity" type="text" placeholder="email@mail.com / 081234567890"></div>
    <div class="form-group"><label>Password</label><input id="loginPassword" type="password" placeholder="Masukkan password"></div>
    <button class="btn primary full" id="loginButton">Login</button>
    <p class="auth-helper">Belum punya akun? <span id="goRegister">Daftar sekarang</span></p>
  `
  document.getElementById('loginButton').onclick = login
  document.getElementById('goRegister').onclick = () => { currentView = 'register'; renderAuthPage() }
}

function renderRegisterForm() {
  document.getElementById('authForm').innerHTML = `
    <div class="form-group"><label>Nama Lengkap</label><input id="registerName" type="text" placeholder="Contoh: Teuku Sultan"></div>
    <div class="form-grid">
      <div class="form-group"><label>Email</label><input id="registerEmail" type="email" placeholder="email@mail.com"></div>
      <div class="form-group"><label>Nomor HP</label><input id="registerPhone" type="text" placeholder="081234567890"></div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label>Tanggal Lahir</label><input id="registerBirthDate" type="date" min="1999-01-01" max="2026-12-31"></div>
      <div class="form-group"><label>Status</label><select id="registerStatus"><option value="Mahasiswa">Mahasiswa</option><option value="Dosen">Dosen</option><option value="Admin">Admin</option><option value="Alumni">Alumni</option><option value="Umum">Umum</option></select></div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label>Password</label><input id="registerPassword" type="password" placeholder="Minimal 6 karakter"></div>
      <div class="form-group"><label>Konfirmasi Password</label><input id="registerConfirmPassword" type="password" placeholder="Ulangi password"></div>
    </div>
    <button class="btn primary full" id="registerButton">Register</button>
    <p class="auth-helper">Sudah punya akun? <span id="goLogin">Login di sini</span></p>
  `
  document.getElementById('registerButton').onclick = register
  document.getElementById('goLogin').onclick = () => { currentView = 'login'; renderAuthPage() }
}

function login() {
  const identity = document.getElementById('loginIdentity').value.trim()
  const password = document.getElementById('loginPassword').value.trim()
  if (!identity || !password) return showToast('Email/nomor HP dan password wajib diisi.')
  const user = db.users.find(item => (item.email === identity || item.phone === identity) && item.password === password)
  if (!user) return showToast('Akun tidak ditemukan atau password salah.')
  currentUser = user
  localStorage.setItem(SESSION_KEY, JSON.stringify(currentUser))
  setPresence(true)
  renderMainApp()
}

function register() {
  const name = document.getElementById('registerName').value.trim()
  const email = document.getElementById('registerEmail').value.trim()
  const phone = document.getElementById('registerPhone').value.trim()
  const birthDate = document.getElementById('registerBirthDate').value
  const status = document.getElementById('registerStatus').value
  const password = document.getElementById('registerPassword').value.trim()
  const confirmPassword = document.getElementById('registerConfirmPassword').value.trim()
  if (!name || !email || !phone || !birthDate || !status || !password || !confirmPassword) return showToast('Semua data register wajib diisi.')
  if (password.length < 6) return showToast('Password minimal 6 karakter.')
  if (password !== confirmPassword) return showToast('Konfirmasi password tidak sama.')
  if (db.users.some(item => item.email === email || item.phone === phone)) return showToast('Email atau nomor HP sudah terdaftar.')

  const newUser = { id: uid(), name, email, phone, birthDate, password, status, bio: status, avatar: '', onlineVisible: true, lastSeenVisible: true }
  db.users.push(newUser)
  saveDatabase()
  currentUser = newUser
  localStorage.setItem(SESSION_KEY, JSON.stringify(currentUser))
  setPresence(true)
  renderMainApp()
}

function renderMainApp() {
  document.body.classList.remove('dark')
  document.getElementById('app').innerHTML = `
    <main class="main-layout">
      <aside class="sidebar">
        <div class="profile-header">
          <div class="profile-click" id="openProfile"><div class="avatar">${getAvatar(currentUser)}</div><div><h3>${escapeHTML(currentUser.name)}</h3><p>${escapeHTML(currentUser.bio || currentUser.status || 'Online')}</p></div></div>
          <button class="logout-btn" id="logoutButton">Logout</button>
        </div>
        <div class="search-box"><input id="searchInput" placeholder="Cari chat, kontak, atau pesan..."></div>
        <div class="tabs">
          <button data-tab="chats" class="${activeTab === 'chats' ? 'active' : ''}">Chat</button>
          <button data-tab="contacts" class="${activeTab === 'contacts' ? 'active' : ''}">Kontak</button>
          <button data-tab="groups" class="${activeTab === 'groups' ? 'active' : ''}">Group</button>
          <button data-tab="archive" class="${activeTab === 'archive' ? 'active' : ''}">Arsip</button>
        </div>
        <div class="quick-actions"><button class="btn primary" id="addContactButton">+ Kontak</button><button class="btn secondary" id="newGroupButton">+ Group</button></div>
        <div class="list" id="sidebarList"></div>
      </aside>
      <section class="chat-panel">
        <div class="empty-state" id="emptyState"><h2>Chattatan</h2><p>Pilih chat atau kontak untuk mulai mengirim pesan.</p></div>
        <div class="chat-room hidden" id="chatRoom">
          <div class="chat-header"><div><h2 id="chatTitle">Chat</h2><p id="chatStatus">online</p></div><div class="header-actions"><button class="icon-btn" id="pinButton">Pin</button><button class="icon-btn" id="archiveButton">Arsip</button><button class="icon-btn" id="infoButton">Info</button></div></div>
          <div class="messages" id="messageList"></div>
          <div class="composer">
            <div class="reply-preview hidden" id="replyPreview"><span id="replyPreviewText"></span><button id="cancelReply">x</button></div>
            <div class="media-preview hidden" id="mediaPreview"></div>
            <div class="composer-row">
              <input type="file" id="filePicker" class="hidden"><input type="file" id="photoPicker" accept="image/*" class="hidden">
              <button class="icon-btn" id="fileButton">File</button><button class="icon-btn" id="photoButton">Gambar</button>
              <textarea id="messageInput" placeholder="Tulis pesan..."></textarea><button class="send-btn" id="sendButton">Kirim</button>
            </div>
          </div>
        </div>
      </section>
    </main>
    <div class="modal hidden" id="modal"></div><div class="toast hidden" id="toast"></div>
  `
  bindMainEvents()
  renderSidebar()
}

function bindMainEvents() {
  document.getElementById('logoutButton').onclick = logout
  document.getElementById('openProfile').onclick = openProfileModal
  document.getElementById('addContactButton').onclick = openContactModal
  document.getElementById('newGroupButton').onclick = openGroupModal
  document.getElementById('searchInput').oninput = renderSidebar
  document.querySelectorAll('.tabs button').forEach(button => button.onclick = () => { activeTab = button.dataset.tab; renderMainApp() })
  document.getElementById('sendButton').onclick = sendMessage
  document.getElementById('messageInput').onkeydown = event => { if (event.key === 'Enter' && !event.shiftKey) { event.preventDefault(); sendMessage() } }
  document.getElementById('messageInput').oninput = sendTyping
  document.getElementById('fileButton').onclick = () => document.getElementById('filePicker').click()
  document.getElementById('photoButton').onclick = () => document.getElementById('photoPicker').click()
  document.getElementById('filePicker').onchange = pickFile
  document.getElementById('photoPicker').onchange = pickPhoto
  document.getElementById('cancelReply').onclick = cancelReply
  document.getElementById('pinButton').onclick = togglePin
  document.getElementById('archiveButton').onclick = toggleArchive
  document.getElementById('infoButton').onclick = openChatInfoModal
}

function currentContacts() {
  return db.contacts.filter(contact => !contact.ownerId || contact.ownerId === currentUser.id)
}

function visibleChats() {
  return db.chats.filter(chat => chat.members.includes(currentUser.id))
}

function renderSidebar() {
  const list = document.getElementById('sidebarList')
  const query = (document.getElementById('searchInput')?.value || '').toLowerCase()
  list.innerHTML = ''

  if (activeTab === 'contacts') {
    currentContacts().filter(contact => `${contact.name} ${contact.phone} ${contact.status || contact.bio || ''}`.toLowerCase().includes(query)).forEach(contact => list.appendChild(createContactItem(contact)))
    return
  }

  let chats = visibleChats()
  if (activeTab === 'archive') chats = chats.filter(chat => isArchived(chat))
  else chats = chats.filter(chat => !isArchived(chat))
  if (activeTab === 'groups') chats = chats.filter(chat => chat.type === 'group')

  chats = chats.filter(chat => `${displayChatName(chat)} ${chat.messages.map(message => message.text).join(' ')}`.toLowerCase().includes(query))
  chats.sort((a, b) => Number(isPinned(b)) - Number(isPinned(a)) || (b.messages.at(-1)?.time || 0) - (a.messages.at(-1)?.time || 0))
  chats.forEach(chat => list.appendChild(createChatItem(chat)))
}

function createContactItem(contact) {
  const element = document.createElement('div')
  element.className = 'list-item contact-item'
  element.innerHTML = `
    <div class="avatar">${getAvatar(contact)}</div>
    <div class="list-content"><div class="list-top"><h4>${escapeHTML(contact.name)}</h4><span>Kontak</span></div><p>${escapeHTML(contact.phone)} • ${escapeHTML(contact.status || contact.bio || '-')}</p></div>
    <div class="item-actions"><button data-action="info">Info</button><button data-action="edit">Edit</button><button data-action="delete">Hapus</button></div>
  `
  element.onclick = () => startPrivateChat(contact)
  element.querySelector('[data-action="info"]').onclick = event => { event.stopPropagation(); openContactInfoModal(contact.id) }
  element.querySelector('[data-action="edit"]').onclick = event => { event.stopPropagation(); openContactModal(contact.id) }
  element.querySelector('[data-action="delete"]').onclick = event => { event.stopPropagation(); deleteContact(contact.id) }
  return element
}

function createChatItem(chat) {
  const last = chat.messages.at(-1)
  const element = document.createElement('div')
  element.className = `list-item ${activeChatId === chat.id ? 'active' : ''}`
  element.innerHTML = `
    <div class="avatar">${getChatAvatar(chat)}</div>
    <div class="list-content"><div class="list-top"><h4>${isPinned(chat) ? '📌 ' : ''}${escapeHTML(displayChatName(chat))}</h4><span>${last ? formatTime(last.time) : ''}</span></div><p>${last ? escapeHTML(`${senderName(last.senderId)}: ${displayMessageText(last) || 'Media'}`) : 'Belum ada pesan'}</p></div>
    ${unreadCount(chat) ? `<div class="badge">${unreadCount(chat)}</div>` : ''}<button class="tiny-action" data-action="archive">${isArchived(chat) ? 'Buka' : 'Arsip'}</button>
  `
  element.onclick = () => openChat(chat.id)
  element.querySelector('[data-action="archive"]').onclick = event => { event.stopPropagation(); toggleArchive(chat.id) }
  return element
}

function startPrivateChat(contact) {
  const linkedUser = getLinkedUser(contact)
  if (!linkedUser) return showToast('Kontak ini belum punya akun. Buat akunnya dulu agar chat bisa tersinkron.')
  if (linkedUser.id === currentUser.id) return showToast('Tidak bisa chat ke akun sendiri.')

  let chat = db.chats.find(item => item.type === 'private' && item.members.includes(currentUser.id) && item.members.includes(linkedUser.id) && item.members.length === 2)
  if (!chat) {
    chat = { id: uid(), type: 'private', name: linkedUser.name, members: [currentUser.id, linkedUser.id], pinnedBy: [], archivedBy: [], unreadBy: {}, messages: [] }
    db.chats.push(chat)
    saveDatabase()
  }
  openChat(chat.id)
}

function openChat(chatId) {
  activeChatId = chatId
  const chat = db.chats.find(item => item.id === chatId && item.members.includes(currentUser.id))
  if (!chat) return
  markChatRead(chat)
  chat.unreadBy[currentUser.id] = 0
  saveDatabase()
  document.getElementById('emptyState').classList.add('hidden')
  document.getElementById('chatRoom').classList.remove('hidden')
  document.getElementById('chatTitle').textContent = displayChatName(chat)
  document.getElementById('chatStatus').textContent = chat.type === 'group' ? `${chat.members.length} anggota • terenkripsi lokal` : `${getPresenceText(otherPrivateMember(chat)?.id)} • terenkripsi lokal`
  renderMessages()
  renderSidebar()
}

function renderMessages() {
  const messageList = document.getElementById('messageList')
  const chat = db.chats.find(item => item.id === activeChatId)
  if (!chat) return
  messageList.innerHTML = ''
  chat.messages.forEach(message => {
    const mine = message.senderId === currentUser.id
    const element = document.createElement('div')
    element.className = `message ${mine ? 'mine' : 'other'}`
    element.innerHTML = `
      ${!mine && chat.type === 'group' ? `<b class="sender">${escapeHTML(senderName(message.senderId))}</b>` : ''}
      ${message.reply ? `<div class="reply-box">Reply: ${escapeHTML(message.reply)}</div>` : ''}
      <p>${escapeHTML(displayMessageText(message))}</p>
      ${message.photo ? `<img class="message-photo" src="${message.photo}" alt="photo">` : ''}
      ${message.file ? `<div class="file-box">📎 ${escapeHTML(message.file.name)} (${escapeHTML(message.file.size)})</div>` : ''}
      <small>${(message.reactions || []).join(' ')} ${formatTime(message.time)} ${mine ? messageReceipt(chat, message) : ''} 🔒</small>
      <div class="message-actions"><button data-action="reply">Reply</button><button data-action="react">React</button>${mine ? `<button data-action="delete">Delete</button>` : ''}</div>
    `
    element.querySelector('[data-action="reply"]').onclick = () => setReply(message)
    element.querySelector('[data-action="react"]').onclick = () => addReaction(message.id)
    if (mine) element.querySelector('[data-action="delete"]').onclick = () => deleteMessage(message.id)
    messageList.appendChild(element)
  })
  messageList.scrollTop = messageList.scrollHeight
}

function sendMessage() {
  const input = document.getElementById('messageInput')
  const text = input.value.trim()
  if (!activeChatId || (!text && !selectedPhoto && !selectedFile)) return
  const chat = db.chats.find(item => item.id === activeChatId && item.members.includes(currentUser.id))
  if (!chat) return showToast('Chat tidak ditemukan.')

  chat.messages.push(makeMessage(currentUser.id, text, { photo: selectedPhoto, file: selectedFile, reply: replyMessage ? displayMessageText(replyMessage) || 'Media' : null, replyToId: replyMessage?.id || null, deliveredTo: chat.members.filter(id => id !== currentUser.id), readBy: [] }))
  chat.members.filter(id => id !== currentUser.id).forEach(id => { chat.unreadBy[id] = (chat.unreadBy[id] || 0) + 1 })
  input.value = ''
  selectedPhoto = null
  selectedFile = null
  replyMessage = null
  document.getElementById('replyPreview').classList.add('hidden')
  document.getElementById('mediaPreview').classList.add('hidden')
  saveDatabase()
  renderMessages()
  renderSidebar()
  channel.postMessage({ type: 'message', chatId: activeChatId })
}

function pickPhoto(event) {
  const file = event.target.files[0]
  if (!file) return
  const reader = new FileReader()
  reader.onload = () => {
    selectedPhoto = reader.result
    selectedFile = null
    document.getElementById('mediaPreview').classList.remove('hidden')
    document.getElementById('mediaPreview').innerHTML = `<img src="${selectedPhoto}" alt="preview"><span>${escapeHTML(file.name)}</span><button id="removeMedia">Hapus</button>`
    document.getElementById('removeMedia').onclick = clearMedia
  }
  reader.readAsDataURL(file)
}

function pickFile(event) {
  const file = event.target.files[0]
  if (!file) return
  selectedFile = { name: file.name, size: `${Math.round(file.size / 1024)} KB` }
  selectedPhoto = null
  document.getElementById('mediaPreview').classList.remove('hidden')
  document.getElementById('mediaPreview').innerHTML = `<span>📎 ${escapeHTML(file.name)}</span><button id="removeMedia">Hapus</button>`
  document.getElementById('removeMedia').onclick = clearMedia
}

function clearMedia() { selectedPhoto = null; selectedFile = null; document.getElementById('mediaPreview').classList.add('hidden') }
function setReply(message) { replyMessage = message; document.getElementById('replyPreview').classList.remove('hidden'); document.getElementById('replyPreviewText').textContent = `Membalas: ${displayMessageText(message) || 'Media'}` }
function cancelReply() { replyMessage = null; document.getElementById('replyPreview').classList.add('hidden') }
function addReaction(messageId) { const chat = db.chats.find(item => item.id === activeChatId); const message = chat.messages.find(item => item.id === messageId); message.reactions.push(['👍', '❤️', '🔥', '👏', '😂'][Math.floor(Math.random() * 5)]); saveDatabase(); renderMessages(); channel.postMessage({ type: 'message', chatId: activeChatId }) }
function deleteMessage(messageId) { const chat = db.chats.find(item => item.id === activeChatId); const message = chat.messages.find(item => item.id === messageId); message.text = 'Pesan ini telah dihapus'; message.encryptedText = encryptText(message.text); message.photo = null; message.file = null; saveDatabase(); renderMessages(); channel.postMessage({ type: 'message', chatId: activeChatId }) }
function sendTyping() { if (activeChatId) channel.postMessage({ type: 'typing', chatId: activeChatId, userId: currentUser.id }) }
function togglePin() { const chat = db.chats.find(item => item.id === activeChatId); if (!chat) return; chat.pinnedBy = isPinned(chat) ? chat.pinnedBy.filter(id => id !== currentUser.id) : [...chat.pinnedBy, currentUser.id]; saveDatabase(); renderSidebar(); showToast(isPinned(chat) ? 'Chat berhasil di-pin.' : 'Pin chat dilepas.') }
function toggleArchive(chatId = activeChatId) { const chat = db.chats.find(item => item.id === chatId); if (!chat) return; chat.archivedBy = isArchived(chat) ? chat.archivedBy.filter(id => id !== currentUser.id) : [...chat.archivedBy, currentUser.id]; saveDatabase(); if (chat.id === activeChatId && isArchived(chat)) { activeChatId = null; document.getElementById('chatRoom')?.classList.add('hidden'); document.getElementById('emptyState')?.classList.remove('hidden') } renderSidebar(); showToast(isArchived(chat) ? 'Chat dipindah ke arsip.' : 'Chat dikeluarkan dari arsip.') }

function openChatInfoModal() {
  const chat = db.chats.find(item => item.id === activeChatId)
  if (!chat) return
  const addable = currentContacts().filter(contact => getLinkedUser(contact) && !chat.members.includes(getLinkedUser(contact).id))
  const addMemberUI = chat.type === 'group' ? `
    <div class="form-group"><label>Tambah Anggota</label><div class="check-list">${addable.map(contact => `<label class="check-item"><input type="checkbox" value="${escapeAttr(getLinkedUser(contact).id)}">${escapeHTML(contact.name)}</label>`).join('') || 'Semua kontak sudah masuk group.'}</div></div>
    <button class="btn secondary" id="addMembersButton">Tambah Anggota</button>` : ''

  openModal(`
    <div class="modal-card"><h2>Info Chat</h2>
      <p><b>Nama:</b> ${escapeHTML(displayChatName(chat))}</p><p><b>Tipe:</b> ${chat.type}</p>
      <p><b>Anggota:</b> ${escapeHTML(chat.members.map(senderName).join(', '))}</p><p><b>Total Pesan:</b> ${chat.messages.length}</p>
      ${addMemberUI}
      <div class="modal-actions"><button class="btn secondary" id="archiveChatButton">${isArchived(chat) ? 'Keluarkan dari Arsip' : 'Arsipkan'}</button><button class="btn danger" id="deleteChatButton">Hapus Chat</button><button class="btn primary" id="closeModalButton">Tutup</button></div>
    </div>`)

  if (chat.type === 'group') document.getElementById('addMembersButton').onclick = () => addMembersToGroup(chat.id)
  document.getElementById('archiveChatButton').onclick = () => { chat.archivedBy = isArchived(chat) ? chat.archivedBy.filter(id => id !== currentUser.id) : [...chat.archivedBy, currentUser.id]; saveDatabase(); closeModal(); renderSidebar() }
  document.getElementById('deleteChatButton').onclick = () => { if (!confirm('Hapus chat ini dari daftar kamu?')) return; chat.archivedBy = [...new Set([...(chat.archivedBy || []), currentUser.id])]; activeChatId = null; saveDatabase(); closeModal(); document.getElementById('chatRoom').classList.add('hidden'); document.getElementById('emptyState').classList.remove('hidden'); renderSidebar() }
  document.getElementById('closeModalButton').onclick = closeModal
}

function addMembersToGroup(chatId) {
  const chat = db.chats.find(item => item.id === chatId)
  const selected = [...document.querySelectorAll('.check-list input:checked')].map(input => input.value)
  if (!selected.length) return showToast('Pilih anggota dulu.')
  chat.members = [...new Set([...chat.members, ...selected])]
  saveDatabase()
  closeModal()
  openChat(chat.id)
  showToast('Anggota group berhasil ditambahkan.')
  channel.postMessage({ type: 'message', chatId: chat.id })
}

function openProfileModal() {
  openModal(`<div class="modal-card"><h2>Profil Saya</h2>
    <div class="profile-photo"><div class="avatar large" id="profileAvatar">${getAvatar(currentUser)}</div><div><input type="file" id="avatarPicker" accept="image/*" class="hidden"><button class="btn secondary" id="avatarButton">Ganti Foto</button><button class="btn ghost" id="removeAvatarButton">Hapus Foto</button><p class="mini-note">Foto tersinkron ke chat, kontak, dan group.</p></div></div>
    <div class="form-group"><label>Nama Lengkap</label><input id="profileName" value="${escapeAttr(currentUser.name)}"></div>
    <div class="form-grid"><div class="form-group"><label>Email</label><input id="profileEmail" value="${escapeAttr(currentUser.email)}"></div><div class="form-group"><label>Nomor HP</label><input id="profilePhone" value="${escapeAttr(currentUser.phone)}"></div></div>
    <div class="form-grid"><div class="form-group"><label>Tanggal Lahir</label><input id="profileBirthDate" type="date" min="1999-01-01" max="2026-12-31" value="${escapeAttr(currentUser.birthDate || '')}"></div><div class="form-group"><label>Status Akun</label><select id="profileStatus"><option value="Mahasiswa" ${currentUser.status === 'Mahasiswa' ? 'selected' : ''}>Mahasiswa</option><option value="Dosen" ${currentUser.status === 'Dosen' ? 'selected' : ''}>Dosen</option><option value="Admin" ${currentUser.status === 'Admin' ? 'selected' : ''}>Admin</option><option value="Alumni" ${currentUser.status === 'Alumni' ? 'selected' : ''}>Alumni</option><option value="Umum" ${currentUser.status === 'Umum' ? 'selected' : ''}>Umum</option></select></div></div>
    <div class="form-group"><label>Bio / Status Profil</label><textarea id="profileBio" rows="3" placeholder="Contoh: Sedang sibuk, Mahasiswa Informatika...">${escapeHTML(currentUser.bio || currentUser.status || '')}</textarea></div>
    <div class="settings-box"><h3>Privasi Status</h3><label class="toggle-row"><input type="checkbox" id="onlineVisible" ${currentUser.onlineVisible !== false ? 'checked' : ''}> Tampilkan online/offline ke kontak</label><label class="toggle-row"><input type="checkbox" id="lastSeenVisible" ${currentUser.lastSeenVisible !== false ? 'checked' : ''}> Tampilkan last seen</label></div>
    <div class="settings-box"><h3>Ganti Password</h3><div class="form-grid"><div class="form-group"><label>Password Lama</label><input id="oldPassword" type="password" placeholder="Isi kalau mau ganti"></div><div class="form-group"><label>Password Baru</label><input id="newPassword" type="password" placeholder="Minimal 6 karakter"></div></div></div>
    <div class="modal-actions"><button class="btn secondary" id="cancelProfileButton">Batal</button><button class="btn primary" id="saveProfileButton">Simpan Profil</button></div></div>`)
  let avatarDraft = currentUser.avatar || ''
  document.getElementById('avatarButton').onclick = () => document.getElementById('avatarPicker').click()
  document.getElementById('removeAvatarButton').onclick = () => { avatarDraft = ''; document.getElementById('profileAvatar').textContent = initials(document.getElementById('profileName').value || currentUser.name) }
  document.getElementById('avatarPicker').onchange = event => { const file = event.target.files[0]; if (!file) return; const reader = new FileReader(); reader.onload = () => { avatarDraft = reader.result; document.getElementById('profileAvatar').innerHTML = `<img src="${avatarDraft}" alt="avatar">` }; reader.readAsDataURL(file) }
  document.getElementById('cancelProfileButton').onclick = closeModal
  document.getElementById('saveProfileButton').onclick = () => saveProfile(avatarDraft)
}

function saveProfile(avatarDraft) {
  const name = document.getElementById('profileName').value.trim()
  const email = document.getElementById('profileEmail').value.trim()
  const phone = document.getElementById('profilePhone').value.trim()
  const birthDate = document.getElementById('profileBirthDate').value
  const status = document.getElementById('profileStatus').value
  const bio = document.getElementById('profileBio').value.trim() || status
  const oldPassword = document.getElementById('oldPassword').value
  const newPassword = document.getElementById('newPassword').value
  if (!name || !email || !phone || !birthDate || !status) return showToast('Nama, email, nomor HP, tanggal lahir, dan status wajib diisi.')
  if (db.users.some(user => user.id !== currentUser.id && (user.email === email || user.phone === phone))) return showToast('Email atau nomor HP sudah dipakai akun lain.')
  if (newPassword) {
    if (oldPassword !== currentUser.password) return showToast('Password lama salah.')
    if (newPassword.length < 6) return showToast('Password baru minimal 6 karakter.')
  }

  const previousPhone = currentUser.phone
  currentUser = {
    ...currentUser,
    name,
    email,
    phone,
    birthDate,
    status,
    bio,
    avatar: avatarDraft,
    onlineVisible: document.getElementById('onlineVisible').checked,
    lastSeenVisible: document.getElementById('lastSeenVisible').checked,
    password: newPassword || currentUser.password
  }
  db.users = db.users.map(user => user.id === currentUser.id ? currentUser : user)
  syncProfileToLinkedContacts(previousPhone)
  localStorage.setItem(SESSION_KEY, JSON.stringify(currentUser))
  saveDatabase()
  channel.postMessage({ type: 'profile', userId: currentUser.id })
  closeModal()
  renderMainApp()
  if (activeChatId) openChat(activeChatId)
  showToast('Profil berhasil diperbarui.')
}

function syncProfileToLinkedContacts(previousPhone = '') {
  db.contacts = db.contacts.map(contact => {
    const linkedById = contact.linkedUserId === currentUser.id
    const linkedByPhone = contact.phone === previousPhone || contact.phone === currentUser.phone
    if (!linkedById && !linkedByPhone) return contact
    return { ...contact, linkedUserId: currentUser.id, phone: currentUser.phone, email: currentUser.email, avatar: currentUser.avatar, status: contact.status || currentUser.status, bio: contact.bio || currentUser.bio }
  })
}

function openContactModal(contactId = null) {
  const contact = contactId ? db.contacts.find(item => item.id === contactId) : null
  openModal(`<div class="modal-card"><h2>${contact ? 'Edit Kontak' : 'Tambah Kontak'}</h2><div class="form-group"><label>Nama Kontak</label><input id="contactName" placeholder="Nama kontak" value="${escapeAttr(contact?.name || '')}"></div><div class="form-group"><label>Nomor HP</label><input id="contactPhone" placeholder="081234567890" value="${escapeAttr(contact?.phone || '')}"></div><div class="form-group"><label>Status</label><select id="contactStatus"><option value="Mahasiswa" ${(contact?.status || contact?.bio) === 'Mahasiswa' ? 'selected' : ''}>Mahasiswa</option><option value="Dosen" ${(contact?.status || contact?.bio) === 'Dosen' ? 'selected' : ''}>Dosen</option><option value="Admin" ${(contact?.status || contact?.bio) === 'Admin' ? 'selected' : ''}>Admin</option><option value="Alumni" ${(contact?.status || contact?.bio) === 'Alumni' ? 'selected' : ''}>Alumni</option><option value="Umum" ${(contact?.status || contact?.bio) === 'Umum' ? 'selected' : ''}>Umum</option></select></div><div class="modal-actions"><button class="btn secondary" id="cancelContactButton">Batal</button><button class="btn primary" id="saveContactButton">${contact ? 'Simpan' : 'Tambah'}</button></div></div>`)
  document.getElementById('cancelContactButton').onclick = closeModal
  document.getElementById('saveContactButton').onclick = () => saveContact(contactId)
}

function saveContact(contactId = null) {
  const name = document.getElementById('contactName').value.trim()
  const phone = document.getElementById('contactPhone').value.trim()
  const status = document.getElementById('contactStatus').value
  if (!name || !phone || !status) return showToast('Nama, nomor HP, dan status wajib diisi.')
  const linked = db.users.find(user => user.phone === phone)
  if (linked?.id === currentUser.id) return showToast('Kontak tidak boleh memakai akun sendiri.')
  if (contactId) {
    db.contacts = db.contacts.map(contact => contact.id === contactId ? { ...contact, name, phone, status, bio: status, email: linked?.email || contact.email || '', linkedUserId: linked?.id || null } : contact)
  } else {
    db.contacts.push({ id: uid(), ownerId: currentUser.id, linkedUserId: linked?.id || null, name, phone, status, bio: status, email: linked?.email || '', avatar: '', onlineVisible: true, lastSeenVisible: true })
  }
  saveDatabase(); closeModal(); renderSidebar(); showToast(contactId ? 'Kontak berhasil diedit.' : 'Kontak berhasil ditambahkan.')
}

function deleteContact(contactId) {
  if (!confirm('Hapus kontak ini?')) return
  db.contacts = db.contacts.filter(contact => contact.id !== contactId)
  saveDatabase(); renderSidebar(); showToast('Kontak berhasil dihapus.')
}

function openContactInfoModal(contactId) {
  const contact = db.contacts.find(item => item.id === contactId)
  const linked = getLinkedUser(contact)
  openModal(`<div class="modal-card"><h2>Info Kontak</h2><div class="profile-photo"><div class="avatar large">${getAvatar(linked || contact)}</div><div><h3>${escapeHTML(contact.name)}</h3><p>${linked ? 'Akun terdaftar' : 'Belum terdaftar'}</p></div></div><p><b>Nomor HP:</b> ${escapeHTML(contact.phone)}</p><p><b>Status:</b> ${escapeHTML(contact.status || contact.bio || linked?.status || linked?.bio || '-')}</p><div class="modal-actions"><button class="btn secondary" id="editInfoContact">Edit</button><button class="btn primary" id="chatInfoContact">Chat</button><button class="btn secondary" id="closeModalButton">Tutup</button></div></div>`)
  document.getElementById('editInfoContact').onclick = () => openContactModal(contactId)
  document.getElementById('chatInfoContact').onclick = () => { closeModal(); startPrivateChat(contact) }
  document.getElementById('closeModalButton').onclick = closeModal
}

function openGroupModal() {
  const options = currentContacts().filter(contact => getLinkedUser(contact)).map(contact => `<label class="check-item"><input type="checkbox" value="${escapeAttr(getLinkedUser(contact).id)}">${escapeHTML(contact.name)}</label>`).join('')
  openModal(`<div class="modal-card"><h2>Buat Group</h2><div class="form-group"><label>Nama Group</label><input id="groupName" placeholder="Contoh: Project Team A"></div><div class="form-group"><label>Pilih Anggota</label><div class="check-list">${options || 'Belum ada kontak yang punya akun.'}</div></div><div class="modal-actions"><button class="btn secondary" id="cancelGroupButton">Batal</button><button class="btn primary" id="saveGroupButton">Buat Group</button></div></div>`)
  document.getElementById('cancelGroupButton').onclick = closeModal
  document.getElementById('saveGroupButton').onclick = () => {
    const name = document.getElementById('groupName').value.trim()
    const members = [...document.querySelectorAll('.check-list input:checked')].map(item => item.value)
    if (!name || members.length < 1) return showToast('Nama group dan minimal satu anggota wajib diisi.')
    db.chats.push({ id: uid(), type: 'group', name, members: [...new Set([currentUser.id, ...members])], pinnedBy: [], archivedBy: [], unreadBy: {}, messages: [] })
    saveDatabase(); closeModal(); activeTab = 'groups'; renderMainApp()
  }
}

function openModal(content) { const modal = document.getElementById('modal'); modal.classList.remove('hidden'); modal.innerHTML = content }
function closeModal() { const modal = document.getElementById('modal'); modal.classList.add('hidden'); modal.innerHTML = '' }
function logout() { setPresence(false); currentUser = null; localStorage.removeItem(SESSION_KEY); localStorage.removeItem(LEGACY_SESSION_KEY); currentView = 'login'; renderAuthPage() }


function encryptText(text = '') {
  try { return btoa(unescape(encodeURIComponent(String(text)))) }
  catch { return String(text) }
}

function decryptText(text = '') {
  if (!text) return ''
  try { return decodeURIComponent(escape(atob(text))) }
  catch { return String(text) }
}

function makeMessage(senderId, text = '', extra = {}) {
  return {
    id: uid(),
    senderId,
    text,
    encryptedText: encryptText(text),
    encrypted: true,
    time: Date.now(),
    photo: null,
    file: null,
    reply: null,
    replyToId: null,
    deliveredTo: [],
    readBy: [],
    reactions: [],
    ...extra
  }
}

function displayMessageText(message) {
  return message?.text || decryptText(message?.encryptedText) || ''
}

function markChatRead(chat) {
  chat.messages.forEach(message => {
    if (message.senderId !== currentUser.id) {
      message.deliveredTo = [...new Set([...(message.deliveredTo || []), currentUser.id])]
      message.readBy = [...new Set([...(message.readBy || []), currentUser.id])]
    }
  })
}

function messageReceipt(chat, message) {
  const receivers = chat.members.filter(id => id !== message.senderId)
  if (!receivers.length) return '✓'
  const allRead = receivers.every(id => (message.readBy || []).includes(id))
  const allDelivered = receivers.every(id => (message.deliveredTo || []).includes(id))
  if (allRead) return '✓✓ Dibaca'
  if (allDelivered) return '✓✓ Terkirim'
  return '✓ Terkirim'
}

function getUser(id) { return db.users.find(user => user.id === id) }
function senderName(id) { return getUser(id)?.name || 'Unknown' }
function getLinkedUser(contact) { return contact?.linkedUserId ? getUser(contact.linkedUserId) : db.users.find(user => user.phone === contact.phone || (contact.email && user.email === contact.email)) }
function otherPrivateMember(chat) { return getUser(chat.members.find(id => id !== currentUser.id)) }
function displayChatName(chat) { return chat.type === 'private' ? (otherPrivateMember(chat)?.name || chat.name || 'Private Chat') : chat.name }
function isPinned(chat) { return (chat.pinnedBy || []).includes(currentUser.id) }
function isArchived(chat) { return (chat.archivedBy || []).includes(currentUser.id) }
function unreadCount(chat) { return (chat.unreadBy || {})[currentUser.id] || 0 }
function getPresenceText(userId) { const user = getUser(userId); if (!user) return 'offline'; if (user.onlineVisible === false) return 'status disembunyikan'; const presence = db.presence[userId]; if (!presence) return 'offline'; if (presence.online) return 'online'; if (user.lastSeenVisible === false) return 'offline'; return `last seen ${new Date(presence.lastSeen).toLocaleString('id-ID')}` }
function getChatAvatar(chat) { if (chat.type === 'group') return 'GR'; return getAvatar(otherPrivateMember(chat) || { name: displayChatName(chat) }) }
function getAvatar(user) { if (user?.linkedUserId) user = getUser(user.linkedUserId) || user; if (user?.avatar) return `<img src="${user.avatar}" alt="avatar">`; return initials(user?.name || 'U') }
function initials(name) { return (name || 'U').split(' ').map(word => word[0]).join('').slice(0, 2).toUpperCase() }
function formatTime(time) { return new Date(time).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }
function escapeHTML(text) { return String(text || '').replace(/[&<>"']/g, value => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[value])) }
function escapeAttr(text) { return escapeHTML(text).replace(/"/g, '&quot;') }
function showToast(message) { let toast = document.getElementById('toast'); if (!toast) { toast = document.createElement('div'); toast.id = 'toast'; toast.className = 'toast'; document.body.appendChild(toast) } toast.textContent = message; toast.classList.remove('hidden'); setTimeout(() => toast.classList.add('hidden'), 2200) }

channel.onmessage = event => {
  db = loadDatabase()
  if (!currentUser) return
  if (event.data.type === 'message') {
    const chat = db.chats.find(item => item.id === event.data.chatId)
    if (chat && chat.members.includes(currentUser.id) && activeChatId !== chat.id) chat.unreadBy[currentUser.id] = chat.unreadBy[currentUser.id] || 0
    saveDatabase()
    if (activeChatId) { const active = db.chats.find(item => item.id === activeChatId); if (active) { markChatRead(active); saveDatabase() } renderMessages() }
    renderSidebar()
  }
  if (event.data.type === 'typing' && event.data.chatId === activeChatId && event.data.userId !== currentUser.id) {
    document.getElementById('chatStatus').textContent = `${senderName(event.data.userId)} sedang mengetik...`
    setTimeout(() => { const chat = db.chats.find(item => item.id === activeChatId); if (!chat) return; document.getElementById('chatStatus').textContent = chat.type === 'group' ? `${chat.members.length} anggota • terenkripsi lokal` : `${getPresenceText(otherPrivateMember(chat)?.id)} • terenkripsi lokal` }, 1200)
  }
  if (event.data.type === 'profile') {
    currentUser = db.users.find(user => user.id === currentUser.id) || currentUser
    if (activeChatId) openChat(activeChatId); else renderMainApp()
  }
  if (event.data.type === 'presence' && activeChatId) {
    const chat = db.chats.find(item => item.id === activeChatId)
    if (chat?.type === 'private') document.getElementById('chatStatus').textContent = getPresenceText(otherPrivateMember(chat)?.id)
  }
}

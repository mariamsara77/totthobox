import Dexie from 'dexie';

export const db = new Dexie('TotthoBoxDB');
db.version(1).stores({
    activities: '++id, type, key, timestamp', 
    settings: 'key, value'
});

export default db;
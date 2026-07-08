function t(){return typeof crypto<"u"&&typeof crypto.randomUUID=="function"?crypto.randomUUID():`req_${Date.now()}_${Math.random().toString(36).slice(2,10)}`}export{t as c};

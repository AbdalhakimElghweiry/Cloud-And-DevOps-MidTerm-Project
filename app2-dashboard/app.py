

import os
from flask import Flask, render_template
import redis


app = Flask(__name__)


REDIS_HOST = os.environ.get("REDIS_HOST", "redis")
REDIS_PORT = int(os.environ.get("REDIS_PORT", 6379))

r = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, decode_responses=True)


MESSAGES_KEY = "messages"
VISITS_KEY   = "visit_count"


@app.route("/")
def dashboard():



    message_count = r.llen(MESSAGES_KEY)


    raw_visits = r.get(VISITS_KEY)
    visit_count = int(raw_visits) if raw_visits is not None else 0


    recent_messages = r.lrange(MESSAGES_KEY, -5, -1)
    recent_messages.reverse()  # Show newest first

    return render_template(
        "dashboard.html",
        message_count=message_count,
        visit_count=visit_count,
        recent_messages=recent_messages,
    )


@app.route("/health")
def health():

    try:
        r.ping()
        return {"status": "ok", "redis": "connected"}, 200
    except Exception as e:
        return {"status": "error", "redis": str(e)}, 500


if __name__ == "__main__":

    app.run(host="0.0.0.0", port=5000, debug=False)

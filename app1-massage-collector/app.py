
import os
from flask import Flask, render_template, request, redirect, url_for
import redis


app = Flask(__name__)


REDIS_HOST = os.environ.get("REDIS_HOST", "redis")
REDIS_PORT = int(os.environ.get("REDIS_PORT", 6379))

r = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, decode_responses=True)

MESSAGES_KEY = "messages"
VISITS_KEY   = "visit_count"


@app.route("/", methods=["GET", "POST"])
def index():



    visit_count = r.incr(VISITS_KEY)

    error_message = None  

    if request.method == "POST":
        message = request.form.get("message", "").strip()

        if message:

            r.rpush(MESSAGES_KEY, message)

            return redirect(url_for("index"))
        else:
            error_message = "Message cannot be empty. Please type something!"

    messages = r.lrange(MESSAGES_KEY, 0, -1)

    return render_template(
        "index.html",
        visit_count=visit_count,
        messages=messages,
        error_message=error_message,
    )


@app.route("/health")
def health():
    """
    Health check endpoint.
    Docker and load balancers use this to verify the service is alive.
    Returns a simple JSON-like response.
    """
    try:
        r.ping()
        return {"status": "ok", "redis": "connected"}, 200
    except Exception as e:
        return {"status": "error", "redis": str(e)}, 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=False)

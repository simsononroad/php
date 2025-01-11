from flask import *


app = Flask(__name__)
app.secret_key = "szupertitkoskulcs"  # Ezt cseréld le egy erősebb kulcsra!



# Előre meghatározott felhasználónév és jelszó


# Főoldal (index)
@app.route("/")
def index():
    return render_template("login_page.html")

@app.route("/login", methods=["POST"])
def login():
    username_html = request.form['username']
    password_html = request.form['password']
    
    
    
    return redirect(url_for("index"))
    

if __name__ == "__main__":
    app.run(debug=True, port=8080)
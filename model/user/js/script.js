function girarRuleta() {
    const resultado = Math.random() < 0.5 ? 'Responder' : 'Fallar';
    if (resultado === 'Responder') {
        if (turno === 1) {
            puntosJugador1 += 15;
            if (puntosJugador1 >= 30) {
                juegosJugador1++;
                puntosJugador1 = 0;
                puntosJugador2 = 0;
                if (juegosJugador1 >= 2) {
                    setsJugador1++;
                    juegosJugador1 = 0;
                    juegosJugador2 = 0;
                    if (setsJugador1 >= 3) {
                        alert('Jugador 1 gana el partido!');
                        resetearJuego();
                    }
                }
            }
        } else {
            puntosJugador2 += 15;
            if (puntosJugador2 >= 30) {
                juegosJugador2++;
                puntosJugador1 = 0;
                puntosJugador2 = 0;
                if (juegosJugador2 >= 2) {
                    setsJugador2++;
                    juegosJugador1 = 0;
                    juegosJugador2 = 0;
                    if (setsJugador2 >= 3) {
                        alert('Jugador 2 gana el partido!');
                        resetearJuego();
                    }
                }
            }
        }
    } else {
        turno = turno === 1 ? 2 : 1;
    }
    actualizarEstado();

    // Verificar si el usuario logra devolver la cola con éxito
    if (resultado === 'Responder' && puntosJugador1 === 30 && juegosJugador1 === 2 && setsJugador1 === 3) {
        alert('¡Has devuelto la cola con éxito!');
    }
}

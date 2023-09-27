/**
 * Returns a Promise object with the rep logs data
 *
 * @param {number} officeId
 * @returns {Promise<Response>}
 */
export function getSeats(officeId) {
	return fetch(`/api/seats.json?office.id=${officeId}`, {
		credentials: 'same-origin'
	})
		.then(response => {
			return response.json();
		});
}

// .then((data) => data['hydra:member'])

export function updateSeatCoords(seatId, coordX, coordY) {
	return fetch(`/api/seats/${seatId}`, {
		method: 'PUT',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			coordX: coordX,
			coordY: coordY,
		})
	})
		.then(response => {
			if (!response.ok) {
				// Handle errors, if the response is not in the 200-299 range
				throw new Error('Network response was not ok');
			}
			return response.json();
		});
}
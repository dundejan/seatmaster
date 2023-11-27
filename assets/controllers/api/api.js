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

			return response;
		});
}

export function updateChairRotation(seatId, rotation) {
	return fetch(`/api/seats/${seatId}`, {
		method: 'PUT',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			rotation: rotation
		})
	})
		.then(response => {
			if (!response.ok) {
				// Handle errors, if the response is not in the 200-299 range
				throw new Error('Network response was not ok');
			}

			return response;
		});
}

export function addSeat(officeId) {
	return fetch(`/api/seats`, {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			office: `/api/offices/${officeId}`,
		})
	})
		.then(async response => {
			if (!response.ok) {
				// Handle errors, if the response is not in the 200-299 range
				throw new Error('Network response was not ok');
			}

			return response;
		});
}

export function updateOfficeSize(officeId, height, width) {
	return fetch(`/api/offices/${officeId}`, {
		method: 'PUT',
		credentials: 'same-origin',
		headers: {
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({
			height: height,
			width: width,
		})
	})
		.then(response => {
			if (!response.ok) {
				// Handle errors, if the response is not in the 200-299 range
				throw new Error('Network response was not ok');
			}

			return response;
		});
}

export function getCurrentAssignments(seatId, dateTimeParam = null) {
	let url = `/ongoing_assignments/seat/${seatId}`;
	if (dateTimeParam) {
		const utcDateTime = new Date(dateTimeParam).toISOString();
		url += `?dateTimeParam=${encodeURIComponent(utcDateTime)}`;
	}

	return fetch(url, {
		credentials: 'same-origin',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		}
	})
		.then(response => {
			if (!response.ok) {
				throw new Error('Network response was not ok: ' + response.statusText);
			}
			return response.json();
		})
		.catch(error => {
			console.error('Fetching assignments failed: ', error);
		});
}
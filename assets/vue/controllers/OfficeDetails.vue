<template>
	<div>
		<h2> Hello {{ office.name }} from Vue! ✅ </h2>
	</div>
	<div>
		<b>This is list of all seats in this office:</b>
		<ul>
			<li v-for="item in office.seats" :key="item.id">{{ item }}</li>
		</ul>
		<b>entrypoint is: {{ entrypoint }}</b>
		<p>Check out the <a :href="entrypoint" class="underline">API Docs</a></p>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';  // Assuming you use Axios for HTTP requests

const { officeId, entrypoint, seats } = defineProps(['officeId', 'entrypoint', 'seats']);
const office = ref({});

onMounted(async () => {
	console.log(seats);

	try {
		const response = await axios.get(`${entrypoint}/offices/${officeId}`);
		office.value = response.data;
		//console.log(response.data);

		for (const item of office.value.seats) {
			try {
				const response = await axios.get(`${item}`);
				//console.log(response.data);
			}
			catch (error) {
				console.error("Failed to fetch office:", error);
			}
		}
	} catch (error) {
		console.error("Failed to fetch office:", error);
	}
});

const getAssignments = async () => {
	try {
		const response = await axios.get('YOUR_ENDPOINT_URL_HERE');
		data.value = response.data;
	} catch (error) {
		console.error("There was an error fetching the data:", error);
	}
};
</script>
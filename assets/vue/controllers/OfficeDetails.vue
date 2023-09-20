<template>
	<div>
		<h2> Hello {{ office.name }} from Vue! ✅ </h2>
	</div>
	<div>
		<b>This is list of all seats in this office:</b>
		<ul>
			<li v-for="item in office.seats" :key="item.id">{{ item }}</li>
		</ul>
	</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';  // Assuming you use Axios for HTTP requests

const { officeId } = defineProps(['officeId']);
const office = ref({});
const seats = ref({});

onMounted(async () => {
	try {
		const response = await axios.get(`https://localhost:8000/api/offices/${officeId}`);
		office.value = response.data;
		console.log(response.data);
	} catch (error) {
		console.error("Failed to fetch office:", error);
	}
});
</script>
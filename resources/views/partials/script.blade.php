<script src="https://unpkg.com/paho-mqtt/mqttws31.min.js"></script>

<!-- <script>
    const MQTTbroker = 'mqtt.beacontelemetry.com'
    const MQTTport = 8083
    const MQTTuser = 'userlog'
    const MQTTpass = 'b34c0n'

    const dataTopics = @json($mqttTopics ?? [])
    const clientID = "clientid_" + Math.floor(Math.random() * 10000)
    const client = new Paho.MQTT.Client(MQTTbroker, MQTTport, clientID)

    client.onConnectionLost = onConnectionLost
    client.onMessageArrived = onMessageArrived

    const options = {
        timeout: 3,
        useSSL: true,
        userName: MQTTuser,
        password: MQTTpass,
        onSuccess: function() {
            dataTopics.forEach(topic => client.subscribe(topic, {
                qos: 0
            }))
        },
        onFailure: function(err) {}
    }

    client.connect(options)

    function onMessageArrived(message) {
        const topic = message.destinationName
        const payload = message.payloadString

        if (!dataTopics.includes(topic)) return

        try {
            const data = JSON.parse(payload)
            if (Object.prototype.hasOwnProperty.call(data, "user_id")) return

            const id_alat = topic
            let display = `TMA: ${data.sensor14} | Status: ${data.sensor13}`
        } catch (e) {}
    }

    function onConnectionLost(responseObject) {}
</script> -->

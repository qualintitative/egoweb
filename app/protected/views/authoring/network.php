                        <div v-if='question.nParams'>
                        <div class="form-group row">
                            <label for="Question_networkRelationshipExprId" class="col-sm-4 col-form-label">Alters are adjacent when</label>
                            <div class="col-sm-8">
                                <b-form-select v-model="question.networkRelationshipExprId"
                                    value-field="id"
                                    id="Question_networkRelationshipExprId"
                                    name="Question[networkRelationshipExprId]"
                                    text-field="name"
                                    :options="question.alterPairExps"
                                    >
                                    <template #first>
                                        <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                    </template>
                                </b-form-select>
                            </div>
                        </div>
                    
                        <input type="hidden" v-model="question.networkParams" name="Question[networkParams]">
                    
                        <div v-if="question.networkRelationshipExprId">
                        <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Node Display</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                    v-model="question.nParams.nodeDisplay"
                                        @change="resetParams('nodeDisplay')"
                                        text-field="name"
                                        value-field="id"
                                        :options="question.alterExps"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                    </div>
                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Node Color</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                    v-if="question.nParams.nodeColor" 
                                    v-model="question.nParams.nodeColor.questionId"
                                        @change="resetParams('nodeColor')"
                                        :options="question.alterQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.nodeColor" 
                                    v-model="question.nParams.nodeColor.options[1].color"
                                        :options="colors"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Default --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            
            
                            <div class="form-group row" v-if="question.nParams.nodeColor" v-for="(item, index) in question.alterQOptions[question.nParams.nodeColor.questionId]">
                                <label class="offset-sm-4 col-sm-5 col-form-label">{{item.name}}</label>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="!isNaN(question.nParams.nodeColor.questionId)" 
                                    v-model="question.nParams.nodeColor.options[index+2].color"
                                        :options="colors"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                    <b-form-select 
                                    v-if="isNaN(question.nParams.nodeColor.questionId)" 
                                    v-model="question.nParams.nodeColor.options[index+2].color"
                                        :options="gradients"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Node Size</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                    v-if="question.nParams.nodeSize" 
                                    v-model="question.nParams.nodeSize.questionId"
                                        @change="resetParams('nodeSize')"
                                        :options="question.alterQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.nodeSize" 
                                    v-model="question.nParams.nodeSize.options[1].size"
                                        :options="nodeSizes"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Default --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row" v-if="question.nParams.nodeSize" v-for="(item, index) in question.alterQOptions[question.nParams.nodeSize.questionId]">
                                <label class="offset-sm-4 col-sm-5 col-form-label">{{item.name}}</label>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.nodeSize" 
                                    v-model="question.nParams.nodeSize.options[index+2].size"
                                        :options="nodeSizes"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
                            
                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Node Shape</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                    v-if="question.nParams.nodeShape" 
                                    v-model="question.nParams.nodeShape.questionId"
                                        @change="resetParams('nodeShape')"
                                        :options="question.alterShapeQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.nodeShape" 
                                    v-model="question.nParams.nodeShape.options[1].shape"
                                        :options="nodeShapes"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Default --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row" v-if="question.nParams.nodeShape" v-for="(item, index) in question.alterQOptions[question.nParams.nodeShape.questionId]">
                                <label class="offset-sm-4 col-sm-5 col-form-label">{{item.name}}</label>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.nodeShape" 
                                    v-model="question.nParams.nodeShape.options[index+2].shape"
                                        :options="nodeShapes"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Edge Color</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                    v-if="question.nParams.edgeColor" 
                                    v-model="question.nParams.edgeColor.questionId"
                                        @change="resetParams('edgeColor')"
                                        :options="question.alterPairQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.edgeColor" 
                                    v-model="question.nParams.edgeColor.options[0].color"
                                        :options="colors"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Default --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row" v-if="question.nParams.edgeColor" v-for="(item, index) in question.alterPairQOptions[question.nParams.edgeColor.questionId]">
                                <label class="offset-sm-4 col-sm-5 col-form-label">{{item.name}}</label>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.edgeColor" 
                                    v-model="question.nParams.edgeColor.options[index+1].color"
                                        :options="colors"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Edge Size</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                    v-if="question.nParams.edgeSize" 
                                    v-model="question.nParams.edgeSize.questionId"
                                        @change="resetParams('edgeSize')"
                                        :options="question.alterPairQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.edgeSize" 
                                    v-model="question.nParams.edgeSize.options[0].size"
                                        :options="edgeSizes"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Default --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
            
                            <div class="form-group row" v-if="question.nParams.edgeSize" v-for="(item, index) in question.alterPairQOptions[question.nParams.edgeSize.questionId]">
                                <label class="offset-sm-4 col-sm-5 col-form-label">{{item.name}}</label>
                                <div class="col-sm-3">
                                    <b-form-select 
                                    v-if="question.nParams.edgeSize" 
                                    v-model="question.nParams.edgeSize.options[index+1].size"
                                        :options="edgeSizes"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
                        </div>
                    

                        <div class="form-group row">
                            <label for="Question_uselfExpression" class="col-sm-4 col-form-label">Create star network with expression</label>
                            <div class="col-sm-8">
                                <b-form-select v-model="question.uselfExpression"
                                    value-field="id"
                                    id="Question_uselfExpression"
                                    name="Question[uselfExpression]"
                                    text-field="name"
                                    :options="question.alterExps"
                                    >
                                    <template #first>
                                        <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                    </template>
                                </b-form-select>
                            </div>
                        </div>
                        <div v-if="question.uselfExpression">

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Ego Label</label>
                                <div class="col-sm-8">
                                <input class="form-control" v-model="question.nParams.egoLabel" @change="forceUpdate">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Ego Node Color</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                        v-model="question.nParams.nodeColor.options[0].color"
                                        :options="colors"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Ego Node Size</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                        v-model="question.nParams.nodeSize.options[0].size"
                                        :options="nodeSizes"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Ego Node Shape</label>
                                <div class="col-sm-5">
                                    <b-form-select 
                                        v-model="question.nParams.nodeShape.options[0].shape"
                                        :options="nodeShapes"
                                        size="xs"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Ego-Alter Edge Color</label>
                                <div class="col-sm-8">
                                    <b-form-select 
                                    v-if="question.nParams.egoEdgeColor" 
                                    v-model="question.nParams.egoEdgeColor.questionId"
                                        @change="resetParams('egoEdgeColor')"
                                        :options="question.alterShapeQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
                            <div class="form-group row" v-if="question.nParams.egoEdgeColor.questionId" v-for="(item, index) in question.alterShapeQOptions[question.nParams.egoEdgeColor.questionId]">
                                <label class="offset-sm-4 col-sm-4 col-form-label">{{item.name}}</label>
                                <div class="col-sm-4">
                                    <b-form-select 
                                    v-if="question.nParams.egoEdgeColor" 
                                    v-model="question.nParams.egoEdgeColor.options[index].color"
                                        :options="colors"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="offset-sm-1 col-sm-3 col-form-label">Ego-AlterEdge Size</label>
                                <div class="col-sm-8">
                                    <b-form-select 
                                    v-if="question.nParams.egoEdgeSize" 
                                    v-model="question.nParams.egoEdgeSize.questionId"
                                        @change="resetParams('egoEdgeSize')"
                                        :options="question.alterShapeQs"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
                            <div class="form-group row" v-if="question.nParams.egoEdgeSize.questionId" v-for="(item, index) in question.alterShapeQOptions[question.nParams.egoEdgeSize.questionId]">
                                <label class="offset-sm-4 col-sm-4 col-form-label">{{item.name}}</label>
                                <div class="col-sm-4">
                                    <b-form-select 
                                    v-if="question.nParams.egoEdgeSize" 
                                    v-model="question.nParams.egoEdgeSize.options[index].size"
                                        :options="edgeSizes"
                                        @change="forceUpdate"
                                        >
                                        <template #first>
                                            <b-form-select-option value="">-- Please select an option --</b-form-select-option>
                                        </template>
                                    </b-form-select>
                                </div>
                            </div>
                        </div>
                    </div>